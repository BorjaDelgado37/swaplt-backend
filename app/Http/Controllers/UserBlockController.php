<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserBlock;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserBlockController extends Controller
{

    //Todos los bloqueos existentes y que responda los nombres de los usuarios, es decir, tienes que hacer una relacion entre la tabla user_blocks y la tabla users para que te devuelva el nombre del usuario el nombre de los usuarios que te han bloqueado
    public function index()
    {
        $bloqueos = UserBlock::with(['blocker', 'blocked'])->get();
        
        $bloqueos = $bloqueos->map(function ($bloqueo) {
            return [
                'usuario_bloqueado' => [
                    'id' => $bloqueo->blocked->id,
                    'nombre' => $bloqueo->blocked->name
                ],
                'usuario_bloqueante' => [
                    'id' => $bloqueo->blocker->id,
                    'nombre' => $bloqueo->blocker->name
                ],
                'razon' => $bloqueo->razon
            ];
        });

        return response()->json([
            'total_bloqueos' => $bloqueos->count(),
            'bloqueos' => $bloqueos
        ]);
    }


    //Desbloquear a un usuario para el panel de administracion, es decir, que el usuario que esta en el panel de administracion pueda desbloquear a otro usuario
    public function desbloquearUsuario(Request $request): JsonResponse
    {
        $request->validate([
            'usuario_bloqueante_id' => 'required|exists:users,id',
            'usuario_bloqueado_id' => 'required|exists:users,id'
        ]);

        // Verificar si existe el bloqueo
        $bloqueo = UserBlock::where('blocker_id', $request->usuario_bloqueante_id)
                          ->where('blocked_id', $request->usuario_bloqueado_id)
                          ->first();

        if (!$bloqueo) {
            return response()->json([
                'message' => 'No existe un bloqueo entre estos usuarios'
            ], 404);
        }

        // Eliminar el bloqueo
        $bloqueo->delete();

        return response()->json([
            'message' => 'Bloqueo eliminado exitosamente',
            'data' => [
                'usuario_bloqueante' => User::find($request->usuario_bloqueante_id)->name,
                'usuario_bloqueado' => User::find($request->usuario_bloqueado_id)->name
            ]
        ]);
    }

    /**
     * Bloquear a un usuario
     */
    public function bloquear(Request $request, User $usuario): JsonResponse
    {
        // No permitir bloquearse a sí mismo
        if ($request->user()->id === $usuario->id) {
            return response()->json([
                'message' => 'No puedes bloquearte a ti mismo'
            ], 400);
        }

        // Verificar si ya está bloqueado
        if ($request->user()->haBloqueadoA($usuario)) {
            return response()->json([
                'message' => 'Ya has bloqueado a este usuario'
            ], 400);
        }

        $bloqueo = $request->user()->bloquearUsuario($usuario, $request->input('razon'));

        return response()->json([
            'message' => 'Usuario bloqueado exitosamente',
            'data' => [
                'usuario_bloqueado' => [
                    'id' => $usuario->id,
                    'nombre' => $usuario->name,
                    'email' => $usuario->email
                ],
                'fecha_bloqueo' => $bloqueo->created_at
            ]
        ]);
    }

    /**
     * Desbloquear a un usuario
     */
    public function desbloquear(Request $request, User $usuario): JsonResponse
    {
        if (!$request->user()->haBloqueadoA($usuario)) {
            return response()->json([
                'message' => 'Este usuario no está bloqueado'
            ], 400);
        }

        $request->user()->desbloquearUsuario($usuario);

        return response()->json([
            'message' => 'Usuario desbloqueado exitosamente',
            'data' => [
                'usuario_desbloqueado' => [
                    'id' => $usuario->id,
                    'nombre' => $usuario->name,
                    'email' => $usuario->email
                ]
            ]
        ]);
    }

    /**
     * Obtener lista de usuarios bloqueados
     */
    public function usuariosBloqueados(Request $request): JsonResponse
    {
        $usuarios = $request->user()->usuariosBloqueados()
            ->select('id', 'name', 'email', 'created_at')
            ->get()
            ->map(function($usuario) {
                return [
                    'id' => $usuario->id,
                    'nombre' => $usuario->name,
                    'email' => $usuario->email,
                    'fecha_registro' => $usuario->created_at,
                    'fecha_bloqueo' => $usuario->bloqueosRecibidos()
                        ->where('blocker_id', auth()->id())
                        ->first()
                        ->created_at
                ];
            });

        return response()->json([
            'total_usuarios_bloqueados' => $usuarios->count(),
            'usuarios_bloqueados' => $usuarios
        ]);
    }

    /**
     * Obtener lista de usuarios que me han bloqueado
     */
    public function usuariosQueMeBloquearon(Request $request): JsonResponse
    {
        $usuarios = $request->user()->usuariosQueMeBloquearon()
            ->select('id', 'name', 'email', 'created_at')
            ->get()
            ->map(function($usuario) {
                return [
                    'id' => $usuario->id,
                    'nombre' => $usuario->name,
                    'email' => $usuario->email,
                    'fecha_registro' => $usuario->created_at,
                    'fecha_bloqueo' => $usuario->bloqueosRealizados()
                        ->where('blocked_id', auth()->id())
                        ->first()
                        ->created_at
                ];
            });

        return response()->json([
            'total_usuarios_que_me_bloquearon' => $usuarios->count(),
            'usuarios_que_me_bloquearon' => $usuarios
        ]);
    }

    /**
     * Verificar si un usuario está bloqueado
     */
    public function verificarBloqueo(Request $request, User $usuario): JsonResponse
    {
        $estaBloqueado = $request->user()->haBloqueadoA($usuario);
        $meHaBloqueado = $request->user()->estaBloqueadoPor($usuario);

        return response()->json([
            'yo_lo_he_bloqueado' => $estaBloqueado,
            'el_me_ha_bloqueado' => $meHaBloqueado,
            'hay_bloqueo' => $estaBloqueado || $meHaBloqueado,
            'usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->name,
                'email' => $usuario->email
            ]
        ]);
    }
} 