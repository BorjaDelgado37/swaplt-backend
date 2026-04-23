<?php

namespace App\Http\Controllers;

use App\Models\Valoracion;
use App\Models\Mensaje;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ValoracionController extends Controller
{
    // Crear una valoración (solo si han intercambiado mensajes)
    public function store(Request $request)
    {
        $request->validate([
            'receptor_id' => 'required|exists:users,id',
            'valor' => 'required|integer|min:1|max:5',
            'comentario' => 'nullable|string|max:255',
        ]);

        $emisor_id = Auth::id();
        $receptor_id = $request->receptor_id;

        if ($emisor_id == $receptor_id) {
            return response()->json(['message' => 'No puedes valorarte a ti mismo'], 403);
        }

        // Comprobar si han intercambiado mensajes
        $hanConversado = Mensaje::where(function($q) use ($emisor_id, $receptor_id) {
            $q->where('emisor_id', $emisor_id)->where('receptor_id', $receptor_id);
        })->orWhere(function($q) use ($emisor_id, $receptor_id) {
            $q->where('emisor_id', $receptor_id)->where('receptor_id', $emisor_id);
        })->exists();

        if (!$hanConversado) {
            return response()->json(['message' => 'Solo puedes valorar a usuarios con los que hayas conversado'], 403);
        }

        // Solo una valoración por emisor-receptor
        if (Valoracion::where('emisor_id', $emisor_id)->where('receptor_id', $receptor_id)->exists()) {
            return response()->json(['message' => 'Ya has valorado a este usuario'], 409);
        }

        $valoracion = Valoracion::create([
            'emisor_id' => $emisor_id,
            'receptor_id' => $receptor_id,
            'valor' => $request->valor,
            'comentario' => $request->comentario,
        ]);

        return response()->json(['message' => 'Valoración registrada', 'data' => $valoracion], 201);
    }

    // Obtener valoraciones de un usuario y su media
    public function valoracionesUsuario($userId)
    {
        $usuario = User::findOrFail($userId);
        // Cargamos la relación con el emisor para cada valoración recibida
        $valoraciones = $usuario->valoracionesRecibidas()->with('emisor')->get();
        $media = $valoraciones->avg('valor');
        $total = $valoraciones->count();

        // Transformamos para incluir el nombre del emisor
        $valoracionesTransformadas = $valoraciones->map(function($valoracion) {
            return [
                'id' => $valoracion->id,
                'emisor_id' => $valoracion->emisor_id,
                'emisor_nombre' => $valoracion->emisor ? $valoracion->emisor->name : null,
                'receptor_id' => $valoracion->receptor_id,
                'valor' => $valoracion->valor,
                'comentario' => $valoracion->comentario,
                'created_at' => $valoracion->created_at,
                'updated_at' => $valoracion->updated_at,
            ];
        });

        return response()->json([
            'usuario_id' => $usuario->id,
            'media_valoracion' => $media,
            'total_valoraciones' => $total,
            'valoraciones' => $valoracionesTransformadas
        ]);
    }
} 