<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WiadomoscController extends Controller
{
    public function create()
    {
        return view('wiadomosc.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tresc' => 'required|string|max:2000',
        ]);

        $webhookUrl = config('services.discord.wiadomosci_webhook');

        Http::post($webhookUrl, [
            'content' => $request->tresc,
        ]);

        return redirect()->route('wiadomosc.create')->with('success', 'Wiadomość została wysłana na Discord.');
    }
}
