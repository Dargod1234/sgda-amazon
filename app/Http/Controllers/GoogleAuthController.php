<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\GoogleToken;

class GoogleAuthController extends Controller
{
    // Redirige al usuario a Google para la autenticación
    public function redirectToOneDrive()
    {
        $clientId = env('ONEDRIVE_CLIENT_ID');
        $redirectUri = env('ONEDRIVE_REDIRECT_URI');
        $url = "https://login.microsoftonline.com/common/oauth2/v2.0/authorize?" . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'Files.ReadWrite.All offline_access', // Permisos para acceso a OneDrive
            'response_mode' => 'query',
            'prompt' => 'consent',
        ]);
        return redirect()->away($url);
    }

    public function handleOneDriveCallback(Request $request)
    {
        $code = $request->input('code');
        $clientId = env('ONEDRIVE_CLIENT_ID');
        $clientSecret = env('ONEDRIVE_CLIENT_SECRET');
        $redirectUri = env('ONEDRIVE_REDIRECT_URI');
        $tenant_id = env('ONEDRIVE_TENANT_ID');
        $response = Http::asForm()->post("https://login.microsoftonline.com/common/oauth2/v2.0/token", [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' =>$code,
            'scope' => 'Files.ReadWrite.All offline_access',
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
        ]);
        $data = $response->json();

        if (isset($data['access_token'])) {

            $token = GoogleToken::updateOrCreate(
                ['id' => 1],
                [
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token']

                ]
            );
            return view('welcome'); // Redirige a la vista deseada
        } else {
            return redirect()->route('login')->withErrors(['error' => 'No se pudo obtener el token de acceso']);
        }
    }

    public function refreshAccessToken()
    {
        $googleToken = GoogleToken::find(1); // Obtén el primer (y único) registro de tokens
        $refreshToken = $googleToken->refresh_token;
        $clientId = env('ONEDRIVE_CLIENT_ID');
        $clientSecret = env('ONEDRIVE_CLIENT_SECRET');
        $tenantId = env('ONEDRIVE_TENANT_ID'); // Asegúrate de tener el tenant_id configurado
        // Realiza la solicitud para refrescar el token de acceso
        $response = Http::asForm()->post("https://login.microsoftonline.com/common/oauth2/v2.0/token", [
            'client_id' => $clientId,
            'refresh_token' => $refreshToken,
            'client_secret' => $clientSecret,
            'scope' => 'Files.ReadWrite.All offline_access',
            'grant_type' => 'refresh_token', // Opcional, basado en los permisos necesarios
        ]);

        $data = $response->json();
        if (isset($data['access_token'])) {
            // Actualiza el token de acceso en la base de datos
            $googleToken->access_token = $data['access_token'];
            $googleToken->refresh_token = $data['refresh_token'];
            $googleToken->save(); // Guarda los cambios en la base de datos
            return response()->json(['message' => 'Token de acceso refrescado correctamente.']);
        } else {
            // Manejar el error si no se pudo refrescar el token
            return redirect()->route('login')->withErrors(['error' => 'No se pudo refrescar el token de acceso']);
        }
    }
}

