<?php

namespace App\Http\Controllers;

use App\Models\Mensaje;
use App\Models\User;
use Illuminate\Http\Request;

class MensajeController extends Controller
{
    // Método auxiliar para verificar si hay bloqueo entre usuarios
    private function verificarBloqueo($emisor_id, $receptor_id)
    {
        $emisor = User::find($emisor_id);
        $receptor = User::find($receptor_id);

        if (!$emisor || !$receptor) {
            return true; // Si alguno de los usuarios no existe, consideramos que hay bloqueo
        }

        return $emisor->estaBloqueadoPor($receptor) || 
               $emisor->haBloqueadoA($receptor) ||
               $receptor->estaBloqueadoPor($emisor) || 
               $receptor->haBloqueadoA($emisor);
    }

    // 📨 1. Enviar un mensaje
    public function store(Request $request)
    {
        $request->validate([
            'emisor_id' => 'required|exists:users,id',
            'receptor_id' => 'required|exists:users,id',
            'contenido' => 'required|string',
        ]);

        // Verificar si hay bloqueo entre usuarios
        if ($this->verificarBloqueo($request->emisor_id, $request->receptor_id)) {
            return response()->json([
                'message' => 'No puedes enviar mensajes a este usuario debido a un bloqueo'
            ], 403);
        }

        $mensaje = Mensaje::create([
            'emisor_id' => $request->emisor_id,
            'receptor_id' => $request->receptor_id,
            'contenido' => $request->contenido,
            'leido' => false,
        ]);

        return response()->json(['message' => 'Mensaje enviado', 'data' => $mensaje], 201);
    }

    // 📩 2. Obtener mensajes entre dos usuarios
    public function index($emisor_id, $receptor_id)
    {
        // Verificar si hay bloqueo entre usuarios
        if ($this->verificarBloqueo($emisor_id, $receptor_id)) {
            return response()->json([
                'message' => 'No puedes ver los mensajes debido a un bloqueo'
            ], 403);
        }

        $mensajes = Mensaje::where(function ($query) use ($emisor_id, $receptor_id) {
            $query->where('emisor_id', $emisor_id)->where('receptor_id', $receptor_id);
        })
            ->orWhere(function ($query) use ($emisor_id, $receptor_id) {
                $query->where('emisor_id', $receptor_id)->where('receptor_id', $emisor_id);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($mensajes);
    }

    // ✅ 3. Marcar mensaje como leído
    public function marcarLeido($id)
    {
        $mensaje = Mensaje::find($id);
        if (!$mensaje) {
            return response()->json(['message' => 'Mensaje no encontrado'], 404);
        }

        // Verificar si hay bloqueo entre usuarios
        if ($this->verificarBloqueo($mensaje->emisor_id, $mensaje->receptor_id)) {
            return response()->json([
                'message' => 'No puedes acceder a este mensaje debido a un bloqueo'
            ], 403);
        }

        $mensaje->update(['leido' => true]);

        return response()->json(['message' => 'Mensaje marcado como leído']);
    }

    // 🗑️ 4. Eliminar un mensaje
    public function destroy($id)
    {
        $mensaje = Mensaje::find($id);
        if (!$mensaje) {
            return response()->json(['message' => 'Mensaje no encontrado'], 404);
        }

        // Verificar si hay bloqueo entre usuarios
        if ($this->verificarBloqueo($mensaje->emisor_id, $mensaje->receptor_id)) {
            return response()->json([
                'message' => 'No puedes acceder a este mensaje debido a un bloqueo'
            ], 403);
        }

        $mensaje->delete();

        return response()->json(['message' => 'Mensaje eliminado con éxito']);
    }
}
