<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        $guzzleClient = new \GuzzleHttp\Client(['verify' => false]);
        return Socialite::driver('google')->setHttpClient($guzzleClient)->stateless()->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $guzzleClient = new \GuzzleHttp\Client(['verify' => false]);
            $googleUser = Socialite::driver('google')->setHttpClient($guzzleClient)->stateless()->user();
            
            // Validación de email verificado
            if (!$googleUser->user['email_verified']) {
                return redirect()->away(env('FRONTEND_URL') . '/login?error=email_not_verified');
            }
            
            // Búsqueda/Creación de usuario
            $user = User::where('google_id', $googleUser->id)
                       ->orWhere('email', $googleUser->email)
                       ->first();

            if (!$user) {
                // Crear nuevo usuario
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'password' => bcrypt(Str::random(16)), // Contraseña aleatoria
                    'email_verified_at' => now(), // El email ya está verificado por Google
                ]);
            } else {
                // Actualizar información del usuario existente
                $user->update([
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                ]);
            }

            // Generar token JWT
            $token = JWTAuth::fromUser($user);

            // Verificar si la petición viene de Postman
            if ($request->header('User-Agent') && str_contains($request->header('User-Agent'), 'Postman')) {
                return response()->json([
                    'token' => $token,
                    'user' => $user
                ]);
            }

            // Si no es Postman, redirigir al frontend
            return redirect()->away(env('FRONTEND_URL') . '/auth/google/callback?token=' . $token);

        } catch (Exception $e) {
            Log::error('Error en autenticación Google: ' . $e->getMessage());
            if ($request->header('User-Agent') && str_contains($request->header('User-Agent'), 'Postman')) {
                return response()->json([
                    'error' => 'Error en la autenticación con Google',
                    'message' => $e->getMessage()
                ], 500);
            }
            return redirect()->away(env('FRONTEND_URL') . '/login?error=google_auth_failed');
        }
    }
} 