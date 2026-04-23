<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MensajeController;
use App\Http\Controllers\VehiculoImagenController;
use App\Http\Controllers\VehiculoReporteController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\FavoritoController;
use App\Http\Controllers\ValoracionController;
use App\Http\Controllers\UserBlockController;
use App\Http\Controllers\GoogleAuthController;

//----------------------------------------   AUTH   ---------------------------------------------//
Route::post('register', [AuthController::class, 'register']);
Route::get('/verify-email/{verification_code}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:api')->get('/me', [AuthController::class, 'me']);
Route::middleware('auth:api')->post('/logout', [AuthController::class, 'logout']);
Route::post('refresh', [AuthController::class, 'refreshToken']);
Route::post('password/reset-request', [AuthController::class, 'requestPasswordReset']); // Ruta para solicitar el restablecimiento de contraseña
Route::get('password/reset/{token}', [AuthController::class, 'showResetForm']); // Nueva ruta para mostrar el formulario
Route::post('password/reset/{token}', [AuthController::class, 'resetPassword']); // Ruta para restablecer la contraseña
Route::put('profile', [AuthController::class, 'updateProfile'])->middleware('auth:api'); // Ruta para actualizar perfil

// Rutas para autenticación con Google
Route::get('auth/google', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);

//----------------------------------------   USERS   ---------------------------------------------//
Route::get('/users', [UserController::class, 'index']); // Mostrar todos los usuarios
Route::get('/users/{id}', [UserController::class, 'show']); // Mostrar un usuario específico
Route::post('/users', [UserController::class, 'store']); // Crear un nuevo usuario
Route::put('/users/{id}', [UserController::class, 'update']); // Actualizar un usuario
Route::delete('/users/{id}', [UserController::class, 'destroy']); // Eliminar un usuario
Route::get('/users/{userId}/vehiculos', [VehiculoController::class, 'getUserVehiclesById']);


//----------------------------------------   VEHICULOS   -----------------------------------------//
Route::get('vehiculos', [VehiculoController::class, 'index']);
Route::get('vehiculos/search', [VehiculoController::class, 'search']);
Route::get('vehiculos/filter', [VehiculoController::class, 'filter']);
Route::get('vehiculos/{id}', [VehiculoController::class, 'show']);
Route::get('vehiculos/{id}/estadisticas-visitas', [VehiculoController::class, 'estadisticasVisitas']);
Route::post('vehiculos', [VehiculoController::class, 'store']);
Route::middleware('auth:api')->put('/vehiculos/{id}', [VehiculoController::class, 'update']);
Route::delete('vehiculos/{id}', [VehiculoController::class, 'destroy']);
Route::middleware('auth:api')->get('user/vehiculos', [VehiculoController::class, 'getUserVehicles']);

//-------------------------------------   CATEGORIAS   -------------------------------------------//
Route::get('categorias', [CategoriaController::class, 'index']);
Route::get('categorias/{id}', [CategoriaController::class, 'show']);
Route::post('categorias', [CategoriaController::class, 'store']);
Route::put('categorias/{id}', [CategoriaController::class, 'update']);
Route::delete('categorias/{id}', [CategoriaController::class, 'destroy']);


//-------------------------------------   FAVORITOS   -------------------------------------------//
Route::middleware(['auth:api'])->group(function () {
    Route::post('favoritos', [FavoritoController::class, 'store']); // Añadir a favoritos
    Route::get('favoritos', [FavoritoController::class, 'index']);  // Obtener favoritos del usuario autenticado
    Route::delete('favoritos/{vehiculo_id}', [FavoritoController::class, 'destroy']); // Eliminar favorito
});


//---------------------------------   REPORTE VEHICULOS   --------------------------------------//
Route::get('vehiculos/reporte/{id}', [VehiculoReporteController::class, 'generarReporte']);

//-------------------------------------   VEHICULO IMAGEN   -------------------------------------------//
Route::post('vehiculos/{vehiculoId}/imagenes', [VehiculoImagenController::class, 'store']);
Route::get('vehiculos/{vehiculoId}/imagenes', [VehiculoImagenController::class, 'show']);
Route::get('vehiculos/{vehiculoId}/primera-imagen', [VehiculoImagenController::class, 'primeraImagen']);
Route::get('vehiculos/imagenes/{id}', [VehiculoImagenController::class, 'mostrarImagen'])->name('vehiculo.imagen');
Route::delete('vehiculos/{vehiculoId}/imagenes/{imagenId}', [VehiculoImagenController::class, 'destroy']);
Route::get('vehiculos/{id}/todas-imagenes', [VehiculoImagenController::class, 'mostrarImagenesVehiculo'])->name('vehiculo.todas.imagenes');


//-------------------------------------   MENSAJES   -------------------------------------------//
Route::post('/mensajes', [MensajeController::class, 'store']);
Route::get('/mensajes/{emisor_id}/{receptor_id}', [MensajeController::class, 'index']);
Route::patch('/mensajes/{id}/leido', [MensajeController::class, 'marcarLeido']);
Route::delete('/mensajes/{id}', [MensajeController::class, 'destroy']);

//----------------------------- VALORACIONES ENTRE USUARIOS -----------------------------//
Route::middleware('auth:api')->post('/valoraciones', [ValoracionController::class, 'store']); // Valorar usuario
Route::get('/users/{userId}/valoraciones', [ValoracionController::class, 'valoracionesUsuario']); // Ver valoraciones de un usuario

//-----------------------------   BLOQUEOS A USUARIOS   -------------------------------------------//
Route::middleware(['auth:api'])->group(function () {
    Route::get('/usuarios/bloqueos-todos', [UserBlockController::class, 'index']);
    Route::post('/usuarios/{usuario}/bloquear', [UserBlockController::class, 'bloquear']);
    Route::post('/usuarios/{usuario}/desbloquear', [UserBlockController::class, 'desbloquear']);
    Route::get('/usuarios/bloqueados', [UserBlockController::class, 'usuariosBloqueados']);
    Route::get('/usuarios/que-me-bloquearon', [UserBlockController::class, 'usuariosQueMeBloquearon']);
    Route::get('/usuarios/{usuario}/verificar-bloqueo', [UserBlockController::class, 'verificarBloqueo']);
});

// Ruta para desbloquear sin autenticación
Route::post('/usuarios/desbloquear-admin', [UserBlockController::class, 'desbloquearUsuario']);
