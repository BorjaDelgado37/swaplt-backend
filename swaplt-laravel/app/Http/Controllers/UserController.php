<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    // Mostrar todos los usuarios
    public function index()
    {
        $users = User::all(); // Obtener todos los usuarios
        return response()->json($users); // Devolver los usuarios en formato JSON
    }

    // Mostrar un usuario específico
    public function show($id)
    {
        $cacheKey = "user_{$id}";
        $user = cache()->remember($cacheKey, now()->addMinutes(30), function () use ($id) {
            return User::select('id', 'name', 'email', 'rol', 'created_at', 'updated_at')
                ->findOrFail($id);
        });
        
        return response()->json($user);
    }

    // Crear un nuevo usuario
    public function store(Request $request)
    {
        // Validar los datos de la solicitud
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'rol' => 'sometimes|string|in:user,admin', // Validar que el rol sea 'user' o 'admin'
        ]);

        // Si los datos no son válidos, devolver un error
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Crear el nuevo usuario
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Cifrar la contraseña
            'rol' => $request->rol ?? 'user', // Asignar rol por defecto si no se pasa
        ]);

        return response()->json($user, 201); // Devolver el usuario creado
    }

    // Actualizar un usuario
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id); // Buscar al usuario por ID

        // Validar los datos de la solicitud
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Si los datos no son válidos, devolver un error
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Actualizar los campos del usuario
        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->has('password')) {
            $user->password = Hash::make($request->password); // Cifrar la nueva contraseña
        }

        $user->save(); // Guardar los cambios

        return response()->json($user); // Devolver el usuario actualizado
    }

    // Eliminar un usuario
    public function destroy($id)
    {
        $user = User::findOrFail($id); // Buscar al usuario por ID
        $user->delete(); // Eliminar el usuario

        return response()->json(null, 204); // Responder con código 204 (sin contenido)
    }
}
