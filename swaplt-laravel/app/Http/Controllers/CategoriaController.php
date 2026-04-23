<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    // Listar todas las categorías (sin restricción, todos los usuarios pueden acceder)
    public function index()
    {
        $categorias = Categoria::all();
        return response()->json($categorias);
    }

    // Crear una nueva categoría (solo admin puede hacerlo)
    public function store(Request $request)
    {

        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $categoria = Categoria::create([
            'nombre' => $request->nombre,
        ]);

        return response()->json($categoria, 201);
    }

    // Obtener una categoría específica
    public function show($id)
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json(['error' => 'Categoría no encontrada'], 404);
        }

        return response()->json($categoria);
    }

    // Actualizar una categoría (solo admin puede hacerlo)
    public function update(Request $request, $id)
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json(['error' => 'Categoría no encontrada'], 404);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $categoria->update([
            'nombre' => $request->nombre,
        ]);

        return response()->json($categoria);
    }

    // Eliminar una categoría (solo admin puede hacerlo)
    public function destroy($id)
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json(['error' => 'Categoría no encontrada'], 404);
        }

        $categoria->delete();
        return response()->json(['message' => 'Categoría eliminada correctamente']);
    }
}
