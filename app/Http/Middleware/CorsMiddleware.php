<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $allowedOrigins = [
            'http://localhost:4200',
            'https://www.swaplt-cars.es',
            'https://swaplt-cars.es',
        ];

        // Añadimos FRONTEND_URL si existe en el .env
        if ($envOrigin = env('FRONTEND_URL')) {
            $allowedOrigins[] = rtrim($envOrigin, '/'); // Eliminar trailing slash por si acaso
        }

        $origin = $request->headers->get('origin');
        
        // Si el origin de la petición está en la lista de permitidos, lo usamos. 
        // Si no, usamos un valor por defecto para no romper el formato.
        $allowOrigin = in_array($origin, $allowedOrigins) ? $origin : $allowedOrigins[0];

        // Manejar preflight OPTIONS
        if ($request->isMethod('OPTIONS')) {
            return response()->json('OK', 200, [
                'Access-Control-Allow-Origin' => $allowOrigin,
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
                'Access-Control-Allow-Credentials' => 'true',
            ]);
        }

        // Continuar con la solicitud y agregar headers CORS
        $response = $next($request);

        $headers = [
            'Access-Control-Allow-Origin' => $allowOrigin,
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
            'Access-Control-Allow-Credentials' => 'true',
        ];

        // Agregamos los headers a la respuesta de forma robusta
        if (method_exists($response, 'withHeaders')) {
            return $response->withHeaders($headers);
        }

        if (isset($response->headers)) {
            foreach ($headers as $key => $value) {
                $response->headers->set($key, $value);
            }
        }

        return $response;
    }
}
