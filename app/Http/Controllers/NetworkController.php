<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

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
        $internalResourceAccess = 'Niedostępne';

        // Sprawdź dostęp do wewnętrznego zasobu tylko w sieci firmowej
        if ($inCompanyNetwork) {
            try {
                $response = Http::timeout(3)->get('http://192.168.210.219/index_mobile.php?ean=1234567890');
                $internalResourceAccess = $response->successful() ? 'Dostępne' : 'Brak dostępu';
            } catch (\Exception $e) {
                $internalResourceAccess = 'Błąd połączenia: ' . $e->getMessage();
            }
        }

        //Log::info("Sprawdzenie połączenia: IP {$ip}, typ: {$connectionType}, dostęp do zasobu: {$internalResourceAccess}");

        return response()->json([
            'connection_type' => $connectionType,
            'internal_resource' => $internalResourceAccess,
            'message' => $inCompanyNetwork 
                ? 'network = WIFI.' 
                : 'network = your computer has a wirus xd.',
        ]);
    }

    public function getClientIp(Request $request)
    {
        $ip = $request->ip();
        //Log::info("Aktualne IP klienta: {$ip}");
        return response()->json([
            'client_ip' => $ip,
        ]);
    }

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