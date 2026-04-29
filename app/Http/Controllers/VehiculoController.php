<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class VehiculoController extends Controller
{
    // Crear un vehículo
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'categoria_id' => 'required|exists:categorias,id',
            'marca' => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
            'precio' => 'required|numeric',
            'anio' => 'required|integer',
            'estado' => 'required|in:nuevo,usado',
            'transmision' => 'required|string|max:255',
            'tipo_combustible' => 'required|string|max:255',
            'kilometraje' => 'required|integer',
            'fuerza' => 'required|integer',
            'capacidad_motor' => 'required|numeric',
            'color' => 'required|string|max:255',
            'ubicacion' => 'required|string|max:255',
            'matricula' => ['required', 'string', 'regex:/^[0-9]{4}[A-Za-z]{3}$/'],
            'numero_serie' => 'required|string|max:255',
            'numero_puertas' => 'required|integer',
            'descripcion' => 'required|string|max:255',
            'vehiculo_robado' => 'required|string|max:255',
            'vehiculo_libre_accidentes' => 'required|string|max:255',
        ]);

        $vehiculo = Vehiculo::create($request->all());

        return response()->json([
            'message' => 'Vehículo creado con éxito',
            'vehiculo' => $vehiculo
        ], 201);
    }

    // Método auxiliar para filtrar vehículos de usuarios bloqueados
    private function filtrarVehiculosBloqueados($query)
    {
        $user = auth()->user();
        if ($user) {
            // Excluir vehículos de usuarios que me han bloqueado
            $usuariosQueMeBloquearon = $user->usuariosQueMeBloquearon()->pluck('id');
            // Excluir vehículos de usuarios que he bloqueado
            $usuariosQueHeBloqueado = $user->usuariosBloqueados()->pluck('id');
            
            $query->whereNotIn('user_id', $usuariosQueMeBloquearon)
                  ->whereNotIn('user_id', $usuariosQueHeBloqueado);
        }
        return $query;
    }

    // Obtener todos los vehículos
    public function index()
    {
        $query = Vehiculo::with(['categoria', 'imagenes'])->orderBy('created_at', 'desc');
        $query = $this->filtrarVehiculosBloqueados($query);
        $vehiculos = $query->paginate(10);
        return response()->json($vehiculos);
    }

    // Obtener un vehículo específico
    public function show($id)
    {
        $vehiculo = Vehiculo::with(['categoria', 'imagenes'])->find($id);

        if (!$vehiculo) {
            return response()->json(['message' => 'Vehículo no encontrado'], 404);
        }

        // Verificar si el usuario está bloqueado
        $user = auth()->user();
        if ($user) {
            $usuarioBloqueado = $user->usuariosQueMeBloquearon()->where('id', $vehiculo->user_id)->exists() ||
                               $user->usuariosBloqueados()->where('id', $vehiculo->user_id)->exists();
            
            if ($usuarioBloqueado) {
                return response()->json(['message' => 'No tienes acceso a este vehículo'], 403);
            }
        }

        // Registrar la visita
        $vehiculo->visitas()->create([
            'user_id' => $user ? $user->id : null,
            'ip_address' => request()->ip(),
            'fecha_visita' => now()
        ]);

        // Transformar las URLs de las imágenes
        $vehiculo->imagenes->transform(function($imagen) {
            $path = storage_path('app/public/' . $imagen->imagen_url);
            $base64 = '';
            
            if (file_exists($path)) {
                $type = mime_content_type($path);
                $base64 = 'data:' . $type . ';base64,' . base64_encode(file_get_contents($path));
            }

            return [
                'id' => $imagen->id,
                'url' => route('vehiculo.imagen', ['id' => $imagen->id]),
                'orden' => $imagen->imagen_order,
                'vehiculo_id' => $imagen->vehiculo_id,
                'preview_url' => $base64
            ];
        });

        return response()->json([
            'vehiculo' => $vehiculo,
            'mensaje' => 'Vehículo encontrado con éxito'
        ]);
    }

    // Actualizar un vehículo
    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'categoria_id' => 'required|exists:categorias,id',
            'marca' => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
            'precio' => 'required|numeric',
            'anio' => 'required|integer',
            'estado' => 'required|in:nuevo,usado',
            'transmision' => 'required|string|max:255',
            'tipo_combustible' => 'required|string|max:255',
            'kilometraje' => 'required|integer',
            'fuerza' => 'required|integer',
            'capacidad_motor' => 'required|numeric',
            'color' => 'required|string|max:255',
            'ubicacion' => 'required|string|max:255',
            'matricula' => ['required', 'string', 'regex:/^[0-9]{4}[A-Za-z]{3}$/'],
            'numero_serie' => 'required|string|max:255',
            'numero_puertas' => 'required|integer',
            'descripcion' => 'required|string|max:255',
            'vehiculo_robado' => 'required|string|max:255',
            'vehiculo_libre_accidentes' => 'required|string|max:255',
        ]);

        $vehiculo = Vehiculo::find($id);

        if (!$vehiculo) {
            return response()->json(['message' => 'Vehículo no encontrado'], 404);
        }

        // Actualiza los datos del vehículo
        $vehiculo->update($request->all());

        return response()->json([
            'message' => 'Vehículo actualizado con éxito',
            'vehiculo' => $vehiculo
        ], 200);
    }


    // Eliminar un vehículo
    public function destroy($id)
    {
        $vehiculo = Vehiculo::find($id);

        if (!$vehiculo) {
            return response()->json(['message' => 'Vehículo no encontrado'], 404);
        }

        $vehiculo->delete();

        return response()->json(['message' => 'Vehículo eliminado con éxito'], 200);
    }

    public function misVehiculos()
    {
        // Obtener el ID del usuario autenticado
        $userId = Auth::id();

        // Buscar los vehículos donde el user_id coincide con el ID del usuario autenticado
        $vehiculos = Vehiculo::where('user_id', $userId)->get();

        return response()->json($vehiculos);
    }

    /**
     * Obtiene todos los vehículos del usuario autenticado mediante JWT
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserVehicles()
    {
        try {
            // Obtener el usuario autenticado mediante JWT
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            // Obtener los vehículos del usuario
            $vehiculos = Vehiculo::where('user_id', $user->id)
                ->with(['categoria', 'imagenes']) // Incluimos relaciones útiles
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $vehiculos,
                'message' => 'Vehículos obtenidos exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los vehículos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Busca vehículos por marca y/o modelo
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        try {
            $query = Vehiculo::query();
            $query = $this->filtrarVehiculosBloqueados($query);

            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('marca', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('modelo', 'LIKE', "%{$searchTerm}%");
                });
            }

            if ($request->has('marca')) {
                $query->where('marca', 'LIKE', "%{$request->marca}%");
            }

            if ($request->has('modelo')) {
                $query->where('modelo', 'LIKE', "%{$request->modelo}%");
            }

            $vehiculos = $query->with(['categoria', 'imagenes'])
                             ->orderBy('created_at', 'desc')
                             ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $vehiculos,
                'message' => 'Búsqueda realizada con éxito'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al realizar la búsqueda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Filtra vehículos según múltiples criterios
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filter(Request $request)
    {
        try {
            $query = Vehiculo::query();
            $query = $this->filtrarVehiculosBloqueados($query);

            // Filtro por rango de precio
            if ($request->has('precio_min') && $request->has('precio_max')) {
                $query->whereBetween('precio', [$request->precio_min, $request->precio_max]);
            } elseif ($request->has('precio_min')) {
                $query->where('precio', '>=', $request->precio_min);
            } elseif ($request->has('precio_max')) {
                $query->where('precio', '<=', $request->precio_max);
            }

            // Filtro por año
            if ($request->has('anio_min') && $request->has('anio_max')) {
                $query->whereBetween('anio', [$request->anio_min, $request->anio_max]);
            } elseif ($request->has('anio_min')) {
                $query->where('anio', '>=', $request->anio_min);
            } elseif ($request->has('anio_max')) {
                $query->where('anio', '<=', $request->anio_max);
            }

            // Filtro por kilometraje máximo
            if ($request->has('kilometraje_max')) {
                $query->where('kilometraje', '<=', $request->kilometraje_max);
            }

            // Filtro por estado (nuevo/usado)
            if ($request->has('estado')) {
                $query->where('estado', $request->estado);
            }

            // Filtro por transmisión
            if ($request->has('transmision')) {
                $query->where('transmision', $request->transmision);
            }

            // Filtro por tipo de combustible
            if ($request->has('tipo_combustible')) {
                $query->where('tipo_combustible', $request->tipo_combustible);
            }

            // Filtro por marca
            if ($request->has('marca')) {
                $query->where('marca', 'LIKE', "%{$request->marca}%");
            }

            // Filtro por modelo
            if ($request->has('modelo')) {
                $query->where('modelo', 'LIKE', "%{$request->modelo}%");
            }

            // Filtro por color
            if ($request->has('color')) {
                $query->where('color', 'LIKE', "%{$request->color}%");
            }

            // Filtro por ubicación
            if ($request->has('ubicacion')) {
                $query->where('ubicacion', 'LIKE', "%{$request->ubicacion}%");
            }

            // Filtro por número de puertas
            if ($request->has('numero_puertas')) {
                $query->where('numero_puertas', $request->numero_puertas);
            }

            // Filtro por vehículo libre de accidentes
            if ($request->has('vehiculo_libre_accidentes')) {
                $query->where('vehiculo_libre_accidentes', $request->vehiculo_libre_accidentes);
            }

            $vehiculos = $query->with(['categoria', 'imagenes'])
                             ->orderBy('created_at', 'desc')
                             ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $vehiculos,
                'message' => 'Filtrado realizado con éxito'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al realizar el filtrado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene todos los vehículos de un usuario específico junto con su información completa
     *
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserVehiclesById($userId)
    {
        try {
            $user = auth()->user();
            if ($user) {
                // Verificar si el usuario está bloqueado
                $usuarioBloqueado = $user->usuariosQueMeBloquearon()->where('id', $userId)->exists() ||
                                   $user->usuariosBloqueados()->where('id', $userId)->exists();
                
                if ($usuarioBloqueado) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No tienes acceso a los vehículos de este usuario'
                    ], 403);
                }
            }

            // Verificar si el usuario existe
            $usuario = \App\Models\User::select('id', 'name', 'email', 'rol', 'created_at', 'updated_at')
                ->find($userId);
            
            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            // Obtener los vehículos del usuario
            $vehiculos = Vehiculo::where('user_id', $userId)
                ->with([
                    'categoria',
                    'imagenes' => function($query) {
                        $query->orderBy('imagen_order', 'asc');
                    }
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            // Transformar las URLs de las imágenes
            $vehiculos->transform(function($vehiculo) {
                $vehiculo->imagenes->transform(function($imagen) {
                    $path = storage_path('app/public/' . $imagen->imagen_url);
                    $base64 = '';
                    
                    if (file_exists($path)) {
                        $type = mime_content_type($path);
                        $base64 = 'data:' . $type . ';base64,' . base64_encode(file_get_contents($path));
                    }

                    return [
                        'id' => $imagen->id,
                        'url' => route('vehiculo.imagen', ['id' => $imagen->id]),
                        'orden' => $imagen->imagen_order,
                        'vehiculo_id' => $imagen->vehiculo_id,
                        'preview_url' => $base64
                    ];
                });
                return $vehiculo;
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'usuario' => [
                        'id' => $usuario->id,
                        'nombre' => $usuario->name,
                        'email' => $usuario->email,
                        'rol' => $usuario->rol,
                        'fecha_registro' => $usuario->created_at,
                        'ultima_actualizacion' => $usuario->updated_at
                    ],
                    'vehiculos' => $vehiculos,
                    'total_vehiculos' => $vehiculos->count()
                ],
                'message' => 'Información del usuario y vehículos obtenida exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la información: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene las estadísticas de visitas de un vehículo
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function estadisticasVisitas($id, Request $request)
    {
        try {
            $vehiculo = Vehiculo::find($id);
            
            if (!$vehiculo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vehículo no encontrado'
                ], 404);
            }

            // Verificar si el usuario es el propietario del vehículo
            $user = auth()->user();
            if (!$user || $user->id !== $vehiculo->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para ver estas estadísticas'
                ], 403);
            }

            $dias = $request->get('dias', 30);
            $estadisticas = $vehiculo->getEstadisticasVisitas($dias);

            // Formatear las fechas para el frontend
            $estadisticas->transform(function($item) {
                return [
                    'fecha' => $item->fecha,
                    'total_visitas' => $item->total_visitas
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'vehiculo_id' => $vehiculo->id,
                    'marca' => $vehiculo->marca,
                    'modelo' => $vehiculo->modelo,
                    'estadisticas' => $estadisticas,
                    'total_visitas' => $estadisticas->sum('total_visitas'),
                    'promedio_diario' => $estadisticas->avg('total_visitas')
                ],
                'message' => 'Estadísticas obtenidas con éxito'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }
}
