<?php

namespace App\Http\Controllers;

use App\Models\Produkt;
use App\Models\EanCode;
use App\Models\Zamowienie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ZamowienieMail;
use App\Exports\ZamowienieExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Pagination\LengthAwarePaginator;

class ProduktController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Produkt $produkt)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Produkt $produkt)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Produkt $produkt)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produkt $produkt)
    {
        //
    }

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
                            ->select('id', 'tw_idabaco', 'tw_nazwa') // Dodajemy tw_idabaco
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

    //dd(request()->all());

    public function zapiszZamowienie(Request $request)
    {
        Log::info('Start metody zapiszZamowienie');

        // Walidacja danych wejściowych
        $validated = $request->validate([
            'zamowienieId' => 'nullable|integer',
            'produkty_json' => 'required|json',
            'wyslij_email' => 'sometimes|boolean'
        ]);

        Log::debug('Zwalidowane dane:', $validated);

        // Rozpocznij transakcję
        DB::beginTransaction();

        try {
            // 1. Utwórz lub zaktualizuj zamówienie
            if (!empty($validated['zamowienieId'])) {
                $zamowienieId = $validated['zamowienieId'];
                Log::info("Używam istniejącego zamówienia ID: $zamowienieId");
            } else {
                $zamowienieId = DB::table('zamowienia')->insertGetId([
                    'data_zamowienia' => now(),
                    'data_realizacji' => null,
                    'automat_id' => null
                ]);
                Log::info("Utworzono nowe zamówienie ID: $zamowienieId");
            }

            // 2. Przetwórz produkty
            $produkty = json_decode($validated['produkty_json'], true);
            Log::debug('Produkty do przetworzenia:', $produkty);

            foreach ($produkty as $index => $produktData) {
                Log::debug("Przetwarzanie produktu #$index", $produktData);

                // 2a. Znajdź lub utwórz produkt
                $produkt = Produkt::firstOrCreate(
                    ['tw_idabaco' => $produktData['tw_idabaco']],
                    [
                        'tw_nazwa' => $produktData['tw_nazwa'],
                        'is_wlasny' => false
                    ]
                );
                Log::debug("Produkt ID: {$produkt->id}, nowy: " . ($produkt->wasRecentlyCreated ? 'tak' : 'nie'));

                // 2b. Dodaj kody EAN dla nowych produktów
                if ($produkt->wasRecentlyCreated && !empty($produktData['ean_codes'])) {
                    foreach ($produktData['ean_codes'] as $kodEan) {
                        EanCode::firstOrCreate([
                            'produkt_id' => $produkt->id,
                            'kod_ean' => $kodEan
                        ]);
                        Log::debug("Dodano kod EAN: $kodEan dla produktu ID: {$produkt->id}");
                    }
                }

                // 2c. Aktualizuj ilość w zamówieniu
                $updated = DB::table('produkt_zamowienie')->updateOrInsert(
                    [
                        'zamowienie_id' => $zamowienieId,
                        'produkt_id' => $produkt->id
                    ],
                    ['ilosc' => $produktData['ilosc']]
                );
                Log::debug("Zaktualizowano ilość: {$produktData['ilosc']} dla produktu ID: {$produkt->id}");
            }

            // 3. Zatwierdź transakcję
            DB::commit();
            Log::info("Pomyślnie zapisano zamówienie ID: $zamowienieId");

            // 4. Obsługa e-maila (opcjonalnie)
            if (!empty($validated['wyslij_email'])) {
                Log::info("Wysyłam maila dla zamówienia ID: $zamowienieId");
                try {
                    $this->wyslijEmailZamowienia($zamowienieId);
                    Log::info("Mail wysłany pomyślnie dla zamówienia ID: $zamowienieId");
                } catch (\Exception $ex) {
                    Log::error("Błąd podczas wysyłania maila: " . $ex->getMessage());
                    // możesz tu dodać jakąś reakcję, np. komunikat błędu, jeśli chcesz
                }
            }

            Log::info("Przekierowanie po zapisie zamówienia");
            return redirect()->route('zamowienia.show', $zamowienieId)
                ->with('success', 'Zamówienie zostało zapisane pomyślnie');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Błąd zapisu zamówienia: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Błąd podczas zapisywania zamówienia: ' . $e->getMessage());
        }
    }



    /**
     * Wyślij email z zamówieniem produktów nie-własnych
     */
    public function wyslijEmailZamowienia($zamowienieId)
    {
        Log::info("Próba wysyłki maila dla zamówienia ID: $zamowienieId");
        
        // Znajdź zamówienie z produktami i ich kodami EAN
        $zamowienie = Zamowienie::with(['produkty' => function($query) {
            $query->select('produkty.id', 'tw_nazwa')
                  ->leftJoin('ean_codes', 'produkty.id', '=', 'ean_codes.produkt_id')
                  ->addSelect('ean_codes.kod_ean as ean');
        }])->findOrFail($zamowienieId);

        Log::debug("Zamówienie załadowane: {$zamowienie->id} z produktami: " . $zamowienie->produkty->count());


        // Generuj plik Excel z dodatkową kolumną EAN
        $xlsxContent = Excel::raw(new ZamowienieExport($zamowienie), \Maatwebsite\Excel\Excel::XLSX);

        Log::debug("Plik Excel wygenerowany. Rozmiar: " . strlen($xlsxContent) . " bajtów");

        // Wyślij email
        try {
            Mail::to(config('mail.importowanie'))->queue(new ZamowienieMail(base64_encode($xlsxContent), $zamowienie));
            Log::info("Mail wrzucony do kolejki dla zamówienia ID: $zamowienieId na adres: " . config('mail.importowanie'));
        } catch (\Exception $e) {
            Log::error("Błąd podczas wrzucania maila do kolejki: " . $e->getMessage());
        }

        return redirect()->route('zamowienia.show', ['zamowienie' => $zamowienieId])
            ->with('success', 'Ilości zostały zapisane.')
            ->with('email_sent', 'Email z zamówieniem został wysłany.');
    }




    /**
     * Pobierz plik Excel z zamówieniem (z kodem EAN)
     */
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
            'ean_codes' => 'nullable|string|max:255', // <- jako string, nie array
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