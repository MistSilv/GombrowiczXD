<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NetworkController extends Controller
{
    public function check(Request $request)
    {
        $ip = $request->ip();

        // Publiczne IP lub zakresy firmy
        $trustedIps = [
            '80.51.20.80', // publiczne ip
        ];

        $inCompanyNetwork = false;
        foreach ($trustedIps as $trustedIp) {
            if ($this->ipMatches($ip, $trustedIp)) {
                $inCompanyNetwork = true;
                break;
            }
        }

        $connectionType = $inCompanyNetwork ? 'wifi_firmowe' : 'sieć_komórkowa';

        Log::info("Sprawdzenie połączenia: IP {$ip}, typ: {$connectionType}");

        return response()->json([
            'connection_type' => $connectionType,
            'message' => $inCompanyNetwork 
                ? 'network = WIFI.' 
                : 'network = your computer has a wirus xd.',
        ]);
    }


    // Nowa metoda do zwracania aktualnego IP klienta (dla testów i zbierania IP)
    public function getClientIp(Request $request)
    {
        $ip = $request->ip();
        Log::info("Aktualne IP klienta: {$ip}");
        return response()->json([
            'client_ip' => $ip,
        ]);
    }

    // Funkcja pomocnicza do sprawdzania dopasowania IP/podsieci
    private function ipMatches($ip, $trusted)
    {
        if (strpos($trusted, '/') === false) {
            return $ip === $trusted;
        }

        [$subnet, $bits] = explode('/', $trusted);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - (int) $bits);

        return ($ip & $mask) === ($subnet & $mask);
    }
}
