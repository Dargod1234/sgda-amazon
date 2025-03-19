<?php

namespace App\Http\Controllers\Auth;

use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Http\Controllers\Controller;
use App\Models\LoginHistory;


class LoginController extends Controller
{
    use AuthenticatesUsers;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    protected function authenticated(Request $request, $user)
    {
        $agent = new Agent();
        $ipinfo = env('IP_INFO');
        // Obtener la información de geolocalización de la IP
        $locationData = Http::get($ipinfo.$request->ip().'/json')->json();
        // Registrar el historial de inicio de sesión
        LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'city' => $locationData['city'] ?? 'Unknown',
            'region' => $locationData['region'] ?? 'Unknown',
            'country' => $locationData['country'] ?? 'Unknown',
            'loc' => $locationData['loc'] ?? 'Unknown',
            'org' => $locationData['org'] ?? 'Unknown',
            'postal' => $locationData['postal'] ?? 'Unknown',
            'timezone' => $locationData['timezone'] ?? 'Unknown',
            'user_agent' => $request->userAgent(),
        ]);


        return redirect()->route('file-system.home');
    }
}
