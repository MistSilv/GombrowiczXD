<?php

namespace App\Http\Controllers;

use App\Models\Zamowienie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    public function handleCallback(Request $request)
    {
        Log::info('Webhook request received', ['data' => $request->all()]);

        try {
            $callback = $request->all()['callback_query'];

            $data = explode('_', $callback['data']);
            $action = $data[0];
            $orderId = $data[1];

            $zamowienie = Zamowienie::with(['automat', 'produkty'])->findOrFail($orderId);

            if ($action === 'realizacja') {
                $zamowienie->status = 'in_progress';
                $status = '🟡 W TRAKCIE';
            } elseif ($action === 'zakonczono') {
                $zamowienie->status = 'completed';
                $status = '✅ ZAKOŃCZONE';
            } else {
                return response()->json(['error' => 'Nieznana akcja'], 400);
            }

            $zamowienie->save();

            $message = "📦 *Zamówienie #{$zamowienie->id}*\n";
            $message .= "Status: {$status}\n";
            $message .= "Automat: {$zamowienie->automat->nazwa}\n";
            $message .= "Data realizacji: {$zamowienie->data_realizacji->format('Y-m-d H:i')}\n\n";
            $message .= "*Produkty:*\n";

            foreach ($zamowienie->produkty as $produkt) {
                $message .= "• {$produkt->tw_nazwa} x {$produkt->pivot->ilosc}\n";
            }

            $keyboard = $action === 'zakonczono' ? null : [
                'inline_keyboard' => [
                    [
                        ['text' => '⚙️ Realizacja', 'callback_data' => "realizacja_{$zamowienie->id}"],
                        ['text' => '⏹️ Zakończono', 'callback_data' => "zakonczono_{$zamowienie->id}"]
                    ]
                ]
            ];

           $payload = [
                'chat_id' => $callback['message']['chat']['id'],
                'message_id' => $callback['message']['message_id'],
                'text' => $message,
                'parse_mode' => 'Markdown',
            ];

            if ($keyboard) {
                $payload['reply_markup'] = json_encode($keyboard);
            }

            $response = Http::post("https://api.telegram.org/bot" . config('services.telegram.bot_token') . "/editMessageText", $payload);

            Log::info('Telegram editMessageText response', ['response' => $response->body()]);


            Http::post("https://api.telegram.org/bot" . config('services.telegram.bot_token') . "/answerCallbackQuery", [
                'callback_query_id' => $callback['id']
            ]);

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            Log::error('Błąd w Telegram webhook: ' . $e->getMessage());
            return response()->json(['error' => 'Błąd przetwarzania webhooka'], 500);
        }
    }
}
