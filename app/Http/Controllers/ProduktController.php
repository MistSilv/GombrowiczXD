<?php

namespace App\Http\Controllers;

use App\Models\Produkt;
use App\Models\EanCode;
use App\Models\Zamowienie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ZamowienieMail;
use App\Exports\ZamowienieExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class ProduktController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(Produkt $produkt) {}
    public function edit(Produkt $produkt) {}
    public function update(Request $request, Produkt $produkt) {}
    public function destroy(Produkt $produkt) {}

    private function buildDeficyty(): \Illuminate\Support\Collection
    {
        $produkty = Produkt::where('is_wlasny', false)->get();

        $wsady = DB::table('produkt_wsad')
            ->join('produkty', 'produkt_wsad.produkt_id', '=', 'produkty.id')
            ->where('produkty.is_wlasny', false)
            ->select('produkty.tw_idabaco', DB::raw('SUM(ilosc) as suma'))
            ->groupBy('produkty.tw_idabaco')
            ->pluck('suma', 'tw_idabaco');

        $zam = DB::table('produkt_zamowienie')
            ->join('zamowienia', 'produkt_zamowienie.zamowienie_id', '=', 'zamowienia.id')
            ->join('produkty', 'produkt_zamowienie.produkt_id', '=', 'produkty.id')
            ->whereNull('zamowienia.automat_id')
            ->where('produkty.is_wlasny', false)
            ->select('produkty.tw_idabaco', DB::raw('SUM(ilosc) as suma'))
            ->groupBy('produkty.tw_idabaco')
            ->pluck('suma', 'tw_idabaco');

        return $produkty
            ->map(function($p) use ($wsady, $zam) {
                $wsadyVal = $wsady[$p->tw_idabaco] ?? 0;
                $zamVal = $zam[$p->tw_idabaco] ?? 0;

                return (object)[
                    'tw_idabaco' => $p->tw_idabaco,
                    'tw_nazwa' => $p->tw_nazwa,
                    'wsady' => $wsadyVal,
                    'zamowienia' => $zamVal,
                    'na_stanie' => ($zamVal) - ($wsadyVal),
                ];
            })
            ->filter(fn($item) => $item->na_stanie !== 0)
            ->values();
    }

    public function formularzNoweZamowienie()
    {
        $deficyty = $this->buildDeficyty();

        $perPage = 30;
        $currentPage = request()->get('page', 1);
        $items = $deficyty->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginated = new LengthAwarePaginator($items, $deficyty->count(), $perPage, $currentPage, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);

        return view('produkty.niewlasne_edit_zamowienie', [
            'produkty' => Produkt::where('is_wlasny', false)
                            ->select('id', 'tw_idabaco', 'tw_nazwa')
                            ->get(),
            'deficyty' => $paginated,
            'zamowienieId' => null,
        ]);
    }

    public function edytujZamowienie($zamowienieId)
    {
        $produkty = DB::table('produkty')
            ->leftJoin('produkt_zamowienie', function($join) use ($zamowienieId) {
                $join->on('produkty.id', '=', 'produkt_zamowienie.produkt_id')
                    ->where('produkt_zamowienie.zamowienie_id', $zamowienieId);
            })
            ->select('produkty.tw_idabaco', 'produkty.tw_nazwa', 'produkt_zamowienie.ilosc')
            ->where('produkty.is_wlasny', false)
            ->get();

        return view('produkty.niewlasne_edit_zamowienie', [
            'produkty' => $produkty,
            'zamowienieId' => $zamowienieId
        ]);
    }

    public function zapiszZamowienie(Request $request)
    {
        Log::info('Start store Zamowienie', ['request_data' => $request->all()]);
        
        $validated = $request->validate([
            'zamowienieId' => 'nullable|integer',
            'produkty_json' => 'required|json',
            'wyslij_email' => 'sometimes|boolean'
        ]);

        DB::beginTransaction();

        try {
            if (!empty($validated['zamowienieId'])) {
                $zamowienieId = $validated['zamowienieId'];
            } else {
                $zamowienieId = DB::table('zamowienia')->insertGetId([
                    'data_zamowienia' => now(),
                    'data_realizacji' => null,
                    'automat_id' => null
                ]);
            }

            $produkty = json_decode($validated['produkty_json'], true);

            foreach ($produkty as $produktData) {
                $produkt = Produkt::firstOrCreate(
                    ['tw_idabaco' => $produktData['tw_idabaco']],
                    [
                        'tw_nazwa' => $produktData['tw_nazwa'],
                        'is_wlasny' => false
                    ]
                );

                if ($produkt->wasRecentlyCreated && !empty($produktData['ean_codes'])) {
                    foreach ($produktData['ean_codes'] as $kodEan) {
                        EanCode::firstOrCreate([
                            'produkt_id' => $produkt->id,
                            'kod_ean' => $kodEan
                        ]);
                    }
                }

                DB::table('produkt_zamowienie')->updateOrInsert(
                    [
                        'zamowienie_id' => $zamowienieId,
                        'produkt_id' => $produkt->id
                    ],
                    ['ilosc' => $produktData['ilosc']]
                );
            }

            DB::commit();

            if (!empty($validated['wyslij_email'])) {
                try {
                    $this->wyslijEmailZamowienia($zamowienieId);
                } catch (\Exception $ex) {
                    // Obsłuż błąd wysyłki maila
                }
            }

            return redirect()->route('zamowienia.show', $zamowienieId)
                ->with('success', 'Zamówienie zostało zapisane pomyślnie');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Błąd podczas zapisywania zamówienia: ' . $e->getMessage());
        }
    }

    public function wyslijEmailZamowienia($zamowienieId)
    {
        $zamowienie = Zamowienie::with(['produkty' => function($query) {
            $query->select('produkty.id', 'tw_nazwa')
                  ->leftJoin('ean_codes', 'produkty.id', '=', 'ean_codes.produkt_id')
                  ->addSelect('ean_codes.kod_ean as ean');
        }])->findOrFail($zamowienieId);

        $xlsxContent = Excel::raw(new ZamowienieExport($zamowienie), \Maatwebsite\Excel\Excel::XLSX);

        try {
            Mail::to(config('mail.importowanie'))->queue(new ZamowienieMail(base64_encode($xlsxContent), $zamowienie));
        } catch (\Exception $e) {
            // Obsłuż błąd maila
        }

        return redirect()->route('zamowienia.show', ['zamowienie' => $zamowienieId])
            ->with('success', 'Ilości zostały zapisane.')
            ->with('email_sent', 'Email z zamówieniem został wysłany.');
    }

    public function pobierzZamowienieExcel($zamowienieId)
    {
        $zamowienie = Zamowienie::with(['produkty' => function($query) {
            $query->select('produkty.id', 'tw_nazwa')
                  ->leftJoin('ean_codes', 'produkty.id', '=', 'ean_codes.produkt_id')
                  ->addSelect('ean_codes.kod_ean as ean');
        }])->findOrFail($zamowienieId);
        
        $date = now()->format('Y-m-d');

        return Excel::download(
            new ZamowienieExport($zamowienie), 
            "zamowienie_{$zamowienieId}_{$date}.xlsx"
        );
    }

    public function createWlasny()
    {
        return view('produkty.create_wlasny');
    }

    public function createNiewlasny()
    {
        return view('produkty.create_wlasny');
    }

    public function storeWlasny(Request $request)
    {
        $validated = $request->validate([
            'tw_nazwa' => 'required|string|max:255',
            'tw_idabaco' => 'nullable|string|max:255',
        ]);

        $validated['is_wlasny'] = true;

        Produkt::create($validated);

        return redirect()->route('produkty.create.wlasny')->with('success', 'Produkt został dodany.');
    }

    public function storeNiewlasny(Request $request)
    {
        $validated = $request->validate([
            'tw_nazwa' => 'required|string|max:255',
            'tw_idabaco' => 'nullable|string|max:255',
            'ean_codes' => 'nullable|string|max:255',
        ]);

        $produkt = Produkt::create([
            'tw_nazwa' => $validated['tw_nazwa'],
            'tw_idabaco' => $validated['tw_idabaco'] ?? null,
            'is_wlasny' => false
        ]);

        if (!empty($validated['ean_codes'])) {
            DB::table('ean_codes')->insert([
                'produkt_id' => $produkt->id,
                'kod_ean' => $validated['ean_codes'],
            ]);
        }

        return redirect()->route('produkty.create.niewlasny')->with('success', 'Produkt został dodany.');
    }

    public function search(Request $request)
    {
        $q = $request->query('q', '');

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $q_normalized = str_replace(' ', '', strtolower($q));

        $produkty = Produkt::select('produkty.id', 'produkty.tw_nazwa', 'produkty.tw_idabaco', 'produkty.is_wlasny')
            ->leftJoin('ean_codes', 'produkty.id', '=', 'ean_codes.produkt_id')
            ->addSelect('ean_codes.kod_ean')
            ->get();

        $results = $produkty->filter(function ($produkt) use ($q_normalized) {
            $name_normalized = str_replace(' ', '', strtolower($produkt->tw_nazwa));
            $ean_normalized = strtolower($produkt->kod_ean ?? '');

            return str_contains($name_normalized, $q_normalized)
                || str_contains($ean_normalized, $q_normalized);
        })->take(20)->values();

        return response()->json($results);
    }
}
