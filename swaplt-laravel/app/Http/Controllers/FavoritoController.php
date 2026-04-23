<?php

namespace App\Http\Controllers;

use App\Models\Favorito;
use Illuminate\Http\Request;

class FavoritoController extends Controller
{
    // Añadir un vehículo a favoritos
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehiculo_id' => 'required|exists:vehiculos,id',
        ]);

        $user_id = auth()->id(); // Obtener el usuario autenticado

        // Verificar si ya está en favoritos
        if (Favorito::where('user_id', $user_id)->where('vehiculo_id', $validated['vehiculo_id'])->exists()) {
            return response()->json(['message' => 'Este vehículo ya está en tus favoritos'], 400);
        }

        // Guardar en favoritos
        $favorito = Favorito::create([
            'user_id' => $user_id,
            'vehiculo_id' => $validated['vehiculo_id'],
        ]);

        return response()->json(['message' => 'Vehículo añadido a favoritos con éxito', 'favorito' => $favorito], 201);
    }

    // Obtener los favoritos del usuario autenticado
    public function index()
    {
        $user_id = auth()->id();
        $favoritos = Favorito::where('user_id', $user_id)->with(['vehiculo.categoria', 'vehiculo.imagenes'])->get();

        return response()->json($favoritos);
    }

    // Eliminar un favorito
    public function destroy($id)
    {
        $favorito = Favorito::find($id);

        if (!$favorito) {
            return response()->json(['error' => 'Favorito no encontrado'], 404);
        }

        $favorito->delete();

        return response()->json(['message' => 'Favorito eliminado exitosamente'], 200);
    }

}
