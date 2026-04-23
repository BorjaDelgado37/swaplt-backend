<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // Registro de usuario
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $verificationCode = bin2hex(random_bytes(16)); // Código de verificación único

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'verification_code' => $verificationCode,
        ]);

        // Enviar correo de verificación
        $this->sendVerificationEmail($user);

        return response()->json([
            'message' => 'Usuario registrado con éxito. Verifica tu correo.',
            'user' => $user
        ]);
    }

    // Enviar correo de verificación
    public function sendVerificationEmail($user)
    {
        // URL base para la verificación web
        $webVerificationUrl = url("/api/verify-email/{$user->verification_code}");

        // Crear el cuerpo del correo
        $emailBody = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1'>
            <title>Verifica tu correo</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    background-color: #f4f4f4; 
                    margin: 0; 
                    padding: 20px;
                }
                .container { 
                    background: white; 
                    padding: 30px; 
                    border-radius: 8px; 
                    box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
                    max-width: 600px;
                    margin: 0 auto;
                }
                .btn { 
                    display: inline-block; 
                    padding: 12px 24px; 
                    margin: 10px 0; 
                    color: white; 
                    background-color: #007BFF; 
                    text-decoration: none; 
                    border-radius: 5px;
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>¡Bienvenido a SWAPLT, {$user->name}!</h2>
                <p>Gracias por registrarte. Para completar tu registro, verifica tu correo electrónico:</p>
                
                <div style='text-align: center;'>
                    <a href='{$webVerificationUrl}' class='btn'>
                        Verificar en el Navegador
                    </a>
                </div>
                
                <p style='margin-top: 30px; font-size: 14px; color: #666;'>
                    Si no solicitaste esta verificación, puedes ignorar este correo.
                </p>
            </div>
        </body>
        </html>";

        // Enviar el correo
        Mail::to($user->email)->send(new class($emailBody) extends \Illuminate\Mail\Mailable {
            public $emailBody;

            public function __construct($emailBody)
            {
                $this->emailBody = $emailBody;
            }

            public function build()
            {
                return $this->subject('Verifica tu cuenta de SWAPLT')
                    ->html($this->emailBody);
            }
        });
    }

    // Verificar email
    public function verifyEmail($verification_code)
    {
        $user = User::where('verification_code', $verification_code)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Código de verificación inválido'
            ], 400);
        }

        if ($user->email_verified_at !== null) {
            return response()->json([
                'success' => true,
                'message' => 'El correo ya ha sido verificado anteriormente'
            ]);
        }

        $user->markEmailAsVerified();
        $user->verification_code = null; // Invalidar el código después de usarlo
        $user->save();

        // Redirigir al frontend
        return redirect('http://localhost:4200/profile');
    }

    // Iniciar sesión
    public function login(Request $request)
    {
        // Validación de los datos de entrada
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Verificar si el email existe en la base de datos
        $user = User::where('email', $request->email)->first();

        // Verificar si el usuario existe y si la contraseña coincide con el hash
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Credenciales incorrectas'], 401);
        }

        // Generar el token JWT
        if ($token = JWTAuth::fromUser($user)) {
            return response()->json(['token' => $token]);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }


    // Obtener usuario autenticado
    public function me()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Token inválido'], 401);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'rol' => $user->rol, // Asegúrate de devolver el rol aquí
            'email_verified_at' => $user->email_verified_at,
            'avatar' => $user->avatar,
        ]);
    }

    // Cerrar sesión
    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Sesión cerrada con éxito']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al cerrar sesión'], 500);
        }
    }

    // Refrescar token
    public function refreshToken(Request $request)
    {
        try {
            $newToken = JWTAuth::parseToken()->refresh();
            return response()->json(['token' => $newToken]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo refrescar el token'], 500);
        }
    }

    // Metodo protegido: solo Admins pueden acceder
    public function adminAction()
    {
        $user = auth()->user();

        if ($user->rol !== 'admin') {
            return response()->json(['error' => 'Acceso denegado, solo Admins pueden realizar esta acción'], 403);
        }

        return response()->json(['message' => 'Acción de administrador realizada']);
    }

    // Solicitar restablecimiento de contraseña
    public function requestPasswordReset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = User::where('email', $request->email)->first();
        $resetToken = bin2hex(random_bytes(32));
        
        // Guardar el token en la base de datos
        $user->reset_token = $resetToken;
        $user->reset_token_expires_at = now()->addHours(1);
        $user->save();

        // Enviar correo con el formulario de restablecimiento
        $this->sendPasswordResetEmail($user, $resetToken);

        return response()->json([
            'message' => 'Se ha enviado un correo con las instrucciones para restablecer tu contraseña'
        ]);
    }

    private function sendPasswordResetEmail($user, $resetToken)
    {
        $resetUrl = url("/api/password/reset/{$resetToken}");
        
        $emailBody = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1'>
            <title>Restablecer Contraseña</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    background-color: #f4f4f4; 
                    margin: 0; 
                    padding: 20px;
                }
                .container { 
                    background: white; 
                    padding: 30px; 
                    border-radius: 8px; 
                    box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
                    max-width: 600px;
                    margin: 0 auto;
                }
                .btn { 
                    display: inline-block; 
                    padding: 12px 24px; 
                    margin: 10px 0; 
                    color: white; 
                    background-color: #007BFF; 
                    text-decoration: none; 
                    border-radius: 5px;
                    font-weight: bold;
                }
                .token-box {
                    background-color: #f8f9fa;
                    padding: 15px;
                    border-radius: 5px;
                    margin: 20px 0;
                    word-break: break-all;
                    font-family: monospace;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Restablecer Contraseña</h2>
                <p>Hola {$user->name},</p>
                <p>Hemos recibido una solicitud para restablecer tu contraseña. Tu token de restablecimiento es:</p>
                
                <div class='token-box'>
                    {$resetToken}
                </div>
                
                <p>Puedes usar este token de dos formas:</p>
                
                <ol>
                    <li>Haz clic en el siguiente botón para restablecer tu contraseña:</li>
                </ol>
                
                <div style='text-align: center;'>
                    <a href='{$resetUrl}' class='btn'>Restablecer Contraseña</a>
                </div>
                
                <ol start='2'>
                    <li>O copia y pega el token en la aplicación cuando te lo solicite.</li>
                </ol>
                
                <p style='margin-top: 30px; font-size: 14px; color: #666;'>
                    Este token expirará en 1 hora. Si no solicitaste este cambio, puedes ignorar este correo.
                </p>
            </div>
        </body>
        </html>";

        Mail::to($user->email)->send(new class($emailBody) extends \Illuminate\Mail\Mailable {
            public $emailBody;

            public function __construct($emailBody)
            {
                $this->emailBody = $emailBody;
            }

            public function build()
            {
                return $this->subject('Restablecer tu contraseña de SWAPLT')
                    ->html($this->emailBody);
            }
        });
    }

    public function showResetForm($token)
    {
        $user = User::where('reset_token', $token)
            ->where('reset_token_expires_at', '>', now())
            ->first();

        if (!$user) {
            return redirect('http://localhost:4200/reset-password?error=invalid_token');
        }

        return redirect("http://localhost:4200/reset-password?token={$token}");
    }

    public function resetPassword(Request $request, $token)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = User::where('reset_token', $token)
            ->where('reset_token_expires_at', '>', now())
            ->first();

        if (!$user) {
            return response()->json([
                'error' => 'El enlace de restablecimiento no es válido o ha expirado'
            ], 400);
        }

        $user->password = Hash::make($request->password);
        $user->reset_token = null;
        $user->reset_token_expires_at = null;
        $user->save();

        return response()->json([
            'message' => 'Contraseña restablecida con éxito'
        ]);
    }

    public function updateProfile(Request $request)
    {
        // Validación de los datos de entrada
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . auth()->id(),  // Verifica que el correo no esté duplicado
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Obtener el usuario autenticado
        $user = auth()->user();

        // Si se ha proporcionado un nuevo nombre, actualizamos
        if ($request->has('name')) {
            $user->name = $request->name;
        }

        // Si se ha proporcionado un nuevo correo, actualizamos
        if ($request->has('email')) {
            $user->email = $request->email;
            $user->verification_code = bin2hex(random_bytes(16)); // Generar un nuevo código de verificación
            $user->email_verified_at = null; // Eliminar la verificación anterior
        }

        // Guardamos el usuario actualizado
        $user->save();

        // Enviar un nuevo correo de verificación al usuario
        $this->sendVerificationEmail($user);

        return response()->json([
            'message' => 'Perfil actualizado con éxito. Verifica tu correo.',
            'user' => $user,
        ]);
    }

}
