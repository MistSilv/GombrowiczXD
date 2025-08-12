<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;

use App\Models\Zamowienie;
use App\Models\Produkt;
use App\Models\Automat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ZamowienieMail;
use App\Exports\ZamowienieExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\PDF;



use Carbon\Carbon;



class ZamowienieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $dzisiaj = Carbon::today()->toDateString();
        $query = Zamowienie::where(function($q) use ($dzisiaj) {
            $q->whereNull('data_realizacji')
            ->orWhere('data_realizacji', '>=', $dzisiaj);
        }); // Filtruj zamówienia, które nie mają daty realizacji lub mają datę realizacji dzisiejszą lub późniejszą

        if ($request->has('automat_id')) {
            $query->where('automat_id', $request->automat_id);
        } // Sprawdź, czy automat_id jest w żądaniu i dodaj warunek

        $zamowienia = $query->orderByDesc('data_zamowienia')->paginate(20)->withQueryString();

        return view('zamowienia.index', compact('zamowienia'));
    }


    /**
     * Show the form for creating a new resource.
     */


   public function create(Request $request)
    {
        // Pobierz tylko produkty własne dla standardowego zamówienia
        $produkty = Produkt::where('is_wlasny', true)->orderBy('tw_nazwa')->get();
        
        $automatId = $request->get('automat_id');
        $automat = $automatId ? Automat::findOrFail($automatId) : null; 

        return view('zamowienia.create', compact('produkty', 'automat'));
    }

    public function createProdukcja(Request $request)
    {
        $produkty = Produkt::where('is_wlasny', true)->orderBy('tw_nazwa')->get();
        $automatId = $request->get('automat_id');
        $automat = $automatId ? Automat::findOrFail($automatId) : null; 

        return view('zamowienia.create', compact('produkty', 'automat'));
    }



    public function store(Request $request)
    {
        $request->validate([
            'produkty' => 'required|array|min:1',
            'produkty.*.produkt_id' => 'required|exists:produkty,id',
            'produkty.*.ilosc' => 'required|integer|min:1|max:3000',
            'automat_id' => 'required|exists:automats,id',
            'data_realizacji' => 'required|in:dzisiaj,jutro',
        ]);

        $zamowienie = Zamowienie::create([
            'data_realizacji' => $request->input('data_realizacji'), // zapisujesz string "dzisiaj" lub "jutro"
            'automat_id' => $request->get('automat_id'),
        ]);

        foreach ($request->produkty as $pozycja) {
            $zamowienie->produkty()->attach($pozycja['produkt_id'], ['ilosc' => $pozycja['ilosc']]);
        }

        $zamowienie->load('produkty', 'automat');

        $produktyText = '';
        foreach ($zamowienie->produkty as $produkt) {
            $produktyText .= "• **{$produkt->tw_nazwa}** — `{$produkt->pivot->ilosc}` szt.\n";
        }

        $message = "📦 *Nowe zamówienie #{$zamowienie->id}*\n";
        $message .= "Status: 🔴 Oczekujące\n"; 
        $message .= "Automat: {$zamowienie->automat->nazwa}\n";
        $message .= "Data realizacji: {$zamowienie->data_realizacji}\n\n";
        $message .= "*Produkty:*\n";

        foreach ($zamowienie->produkty as $produkt) {
            $message .= "• {$produkt->tw_nazwa} x {$produkt->pivot->ilosc}\n";
        }

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '⚙️ Realizacja', 'callback_data' => "realizacja_{$zamowienie->id}"],
                    ['text' => '⏹️ Zakończono', 'callback_data' => "zakonczono_{$zamowienie->id}"]
                ]
            ]
        ];

    Http::post("https://api.telegram.org/bot" . config('services.telegram.bot_token') . "/sendMessage", [
        'chat_id' => config('services.telegram.chat_id'),
        'text' => $message,
        'parse_mode' => 'Markdown',
        'reply_markup' => json_encode($keyboard)
    ]);


        return redirect()->route('zamowienia.index', ['automat_id' => $request->get('automat_id')])
            ->with('success', 'Zamówienie zostało zapisane i wysłane na Discord.');
    }


    public function archiwum(Request $request)
    {
        $query = Zamowienie::query();

        $minRaw = $request->min_date;
        $maxRaw = $request->max_date;

        if ($request->filled('min_date') && $request->filled('max_date')) {
            $minDate = $minRaw . ' 00:00:00';
            $maxDate = $maxRaw . ' 23:59:59';
            $query->whereBetween('data_zamowienia', [$minDate, $maxDate]);
        } else {
            if ($request->filled('min_date')) {
                $query->where('data_zamowienia', '>=', $minRaw . ' 00:00:00');
            }
            if ($request->filled('max_date')) {
                $query->where('data_zamowienia', '<=', $maxRaw . ' 23:59:59');
            }
        }

        $zamowienia = $query->orderByDesc('data_zamowienia')->paginate(20)->withQueryString();

        return view('zamowienia.archiwum', compact('zamowienia'));
    }





    /**
     * Display the specified resource.
     */
   public function show(Zamowienie $zamowienie)
    {
        $zamowienie->load('automat');

        // Paginacja produktów
        $produkty = $zamowienie->produkty()->paginate(10)->withQueryString();

        return view('zamowienia.show', compact('zamowienie', 'produkty'));
    }




    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Zamowienie $zamowienie)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Zamowienie $zamowienie)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Zamowienie $zamowienie)
    {
        //
    }


    //testowe rzeczy tutaj potem się zakomentuuje inacznej
    public function pobierzZamowienieXlsx($id)
    {
        $zamowienie = Zamowienie::with('produkty')->findOrFail($id);

        return Excel::download(new ZamowienieExport($zamowienie), "zamowienie_{$id}.xlsx");
    }

    public function pobierzZamowienieCsv($id)
    {
        $zamowienie = Zamowienie::with('produkty')->findOrFail($id);

        return Excel::download(new ZamowienieExport($zamowienie), "zamowienie_{$id}.csv", \Maatwebsite\Excel\Excel::CSV);
    }


}
