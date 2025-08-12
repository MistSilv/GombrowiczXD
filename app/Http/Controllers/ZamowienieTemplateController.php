<?php

namespace App\Http\Controllers;

use App\Models\ZamowienieTemplate;
use App\Models\Produkt;
use App\Models\EanCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;

class ZamowienieTemplateController extends Controller
{
    // Lista wszystkich szablonów
    public function index()
    {
        $templates = ZamowienieTemplate::with('produkty.produkt')->get();
        return view('produkty.templates.index', compact('templates'));
    }

    // Formularz tworzenia nowego szablonu
    public function create()
    {
        // Pobierz wszystkie produkty do wyboru
        $produkty = Produkt::all();
        return view('produkty.templates.create', compact('produkty'));
    }

    // Zapisz nowy szablon wraz z produktami

    public function store(Request $request)
    {
        Log::info('Start store ZamowienieTemplate', ['request_data' => $request->all()]);

        $validated = $request->validate([
            'nazwa' => 'required|string|max:255',
            'produkty_json' => 'required|json'
        ]);

        DB::beginTransaction();

        try {
            // Tworzymy szablon
            $templateId = DB::table('zamowienie_template')->insertGetId([
                'nazwa' => $validated['nazwa'],

            ]);

            $produkty = json_decode($validated['produkty_json'], true);

            foreach ($produkty as $produktData) {
                Log::info('Przetwarzam produkt', $produktData);

                // Produkt — jeśli nie istnieje, tworzymy
                $produkt = Produkt::firstOrCreate(
                    ['tw_idabaco' => $produktData['tw_idabaco']],
                    [
                        'tw_nazwa' => $produktData['tw_nazwa'] ?? 'Brak nazwy',
                        'is_wlasny' => false
                    ]
                );

                // Dodaj EAN-y tylko przy tworzeniu nowego
                if ($produkt->wasRecentlyCreated && !empty($produktData['ean_codes'])) {
                    foreach ($produktData['ean_codes'] as $kodEan) {
                        EanCode::firstOrCreate([
                            'produkt_id' => $produkt->id,
                            'kod_ean' => $kodEan
                        ]);
                    }
                }

                // Zapis do pivot table szablon–produkt
                DB::table('zamowienie_template_produkt')->updateOrInsert(
                    [
                        'zamowienie_template_id' => $templateId, // ✅ poprawna kolumna
                        'produkt_id' => $produkt->id
                    ],
                    ['ilosc' => $produktData['ilosc']]
                );

            }

            DB::commit();

            return redirect()
                ->route('templates.show', $templateId)
                ->with('success', 'Szablon zamówienia zapisany pomyślnie');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Błąd zapisu szablonu', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'Błąd podczas zapisywania szablonu: ' . $e->getMessage());
        }
    }

 
    public function show($id)
    {
        $template = ZamowienieTemplate::with('produkty.produkt')->findOrFail($id);
        return view('produkty.templates.create', [
            'template' => $template,
            'produkty' => $template->produkty 
        ]);
    }


    public function update(Request $request, $id)
    {
        $template = ZamowienieTemplate::findOrFail($id);

        // Sprawdź czy przesłano ilości
        $ilosci = $request->input('ilosci', []);

        foreach ($ilosci as $pivotId => $ilosc) {
            // Zaktualizuj ilość w tabeli pivot
            DB::table('zamowienie_template_produkt')
                ->where('id', $pivotId)
                ->where('zamowienie_template_id', $template->id)
                ->update(['ilosc' => $ilosc]);
        }

        return redirect()->route('produkty.templates.index')
            ->with('success', 'Ilości zostały zaktualizowane.');
    }

 
    public function destroy($id)
    {
        $template = ZamowienieTemplate::findOrFail($id);

        // Usuwa powiązane produkty z pivotu ręcznie
        DB::table('zamowienie_template_produkt')->where('zamowienie_template_id', $template->id)->delete();

        // Usuwa szablon
        $template->delete();

        return redirect()->route('produkty.templates.index')
            ->with('success', 'Szablon zamówienia został usunięty.');
    }
}
