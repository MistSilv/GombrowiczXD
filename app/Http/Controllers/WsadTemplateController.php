<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wsad;
use App\Models\ProduktWsad;
use App\Models\WsadTemplate;
use App\Models\WsadTemplateProdukt;
use App\Models\Automat;
use App\Models\Produkt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class WsadTemplateController extends Controller
{
    public function index()
    {
        $automaty = Automat::paginate(9);
        return view('automats.wsad_template.index', compact('automaty'));
    }

    public function show($automatId)
    {
        $automat = Automat::with(['wsadTemplates.produkty.produkt'])->findOrFail($automatId);

        $templatesArray = $automat->wsadTemplates->map(function ($t) {
            return [
                'id' => $t->id,
                'nazwa' => $t->nazwa,
                'produkty' => $t->produkty->map(function ($p) {
                    return [
                        'nazwa' => $p->produkt ? $p->produkt->tw_nazwa : '???',
                        'ilosc' => $p->ilosc,
                    ];
                })->toArray(),
            ];
        })->toArray();

        $produkty = Produkt::select('id', 'tw_nazwa')->get();

        return view('automats.wsad_template.show', compact('automat', 'templatesArray', 'produkty'));
    }

    public function create($automatId)
    {
        $automat = Automat::findOrFail($automatId);
        $produkty = Produkt::all();

        return view('automats.wsad_template.create', compact('automat', 'produkty'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'automat_id' => 'required|exists:automats,id',
            'nazwa' => 'nullable|string|max:255',
            'produkty' => 'required|array|min:1',
            'produkty.*.produkt_id' => ['required', 'integer', Rule::exists('produkty', 'id')],
            'produkty.*.ilosc' => 'required|integer|min:1',
        ]);

        $wsadTemplate = WsadTemplate::create([
            'automat_id' => $request->automat_id,
            'nazwa' => $request->nazwa ?? 'Nowy szablon',
            'is_active' => true,
        ]);

        foreach ($request->produkty as $produkt) {
            WsadTemplateProdukt::create([
                'wsad_template_id' => $wsadTemplate->id,
                'produkt_id' => $produkt['produkt_id'],
                'ilosc' => $produkt['ilosc'],
            ]);
        }

        return redirect()->back()->with('success', 'Szablon dodany i ustawiony jako aktywny.');
    }

    public function update(Request $request, WsadTemplate $template)
    {

        $request->validate([
            'nazwa' => 'nullable|string|max:255',
        ]);

        $template->nazwa = $request->nazwa;
        $template->save();

        return redirect()->back()->with('success', 'Nazwa szablonu zaktualizowana.');
    }

    public function destroy(WsadTemplate $template)
    {
        $template->produkty()->delete();
        $template->delete();

        return redirect()->back()->with('success', 'Szablon usunięty.');
    }

    public function activate(WsadTemplate $template)
    {

        $template->is_active = true;
        $template->save();

        return redirect()->back()->with('success', 'Szablon ustawiony jako aktywny.');
    }

    public function deactivate($id)
    {
        $template = WsadTemplate::findOrFail($id);
        $template->is_active = false;
        $template->save();

        return redirect()->back()->with('success', 'Szablon dezaktywowany.');
    }

    public function removeProduct($templateId, $produktId)
    {
        WsadTemplateProdukt::where('wsad_template_id', $templateId)
            ->where('produkt_id', $produktId)
            ->delete();

        return redirect()->back()->with('success', 'Produkt usunięty z szablonu.');
    }

    public function updateIlosc(Request $request, WsadTemplate $template)
    {

        $data = $request->validate([
            'produkty' => 'required|array',
            'produkty.*.produkt_id' => ['required', 'integer', Rule::exists('produkty', 'id')],
            'produkty.*.ilosc' => 'required|integer|min:0',
        ]);

        foreach ($data['produkty'] as $p) {
            $wsadProdukt = WsadTemplateProdukt::where('wsad_template_id', $template->id)
                ->where('produkt_id', $p['produkt_id'])
                ->first();

            if ($wsadProdukt) {
                $wsadProdukt->ilosc = $p['ilosc'];
                $wsadProdukt->save();
            }
        }

        return response()->json(['success' => true]);
    }

    public function fullUpdate(Request $request, WsadTemplate $template)
    {
        Log::info($request->all());

        $data = $request->validate([
            'nazwa' => 'required|string|max:255',
            'produkty' => 'array',
            'produkty.*.produkt_id' => ['required', 'integer', Rule::exists('produkty', 'id')],
            'produkty.*.ilosc' => 'required|integer|min:1',
        ]);


        DB::transaction(function () use ($template, $data) {
            $template->update(['nazwa' => $data['nazwa']]);
            // Usuń wszystkie produkty powiązane ze szablonem
            $template->produkty()->delete();

            foreach ($data['produkty'] as $p) {
                $template->produkty()->create([
                    'produkt_id' => $p['produkt_id'],
                    'ilosc' => $p['ilosc'],
                ]);
            }
        });

        return response()->json(['success' => true]);
    }

    // Opcjonalnie jeśli chcesz: tworzenie wsadu na podstawie szablonu
    public function storeFromTemplate(Request $request)
    {
        $request->validate([
            'wsad_template_id' => 'required|exists:wsad_templates,id',
        ]);

        $template = WsadTemplate::with('produkty')->findOrFail($request->wsad_template_id);

        DB::transaction(function () use ($template) {
            $wsad = Wsad::create([
                'automat_id' => $template->automat_id,
            ]);

            foreach ($template->produkty as $item) {
                ProduktWsad::create([
                    'wsad_id' => $wsad->id,
                    'produkt_id' => $item->produkt_id,
                    'ilosc' => $item->ilosc,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Wsad utworzony na podstawie szablonu.');
    }
}
