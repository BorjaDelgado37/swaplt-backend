<?php

// app/Http/Controllers/VehiculoImagenController.php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use App\Models\VehiculoImagen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VehiculoImagenController extends Controller
{
    // Función para subir imágenes de un vehículo
    public function store(Request $request, $vehiculoId)
    {
        $request->validate([
            'imagenes.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $vehiculo = Vehiculo::find($vehiculoId);
        if (!$vehiculo) {
            return response()->json(['message' => 'Vehículo no encontrado'], 404);
        }

        $imagenesSubidas = [];
        foreach ($request->file('imagenes') as $imagen) {
            $imagePath = $imagen->store('vehiculos_imagenes', 'public');

            $vehiculoImagen = new VehiculoImagen();
            $vehiculoImagen->vehiculo_id = $vehiculoId;
            $vehiculoImagen->imagen_url = $imagePath;
            $vehiculoImagen->imagen_path = Storage::disk('public')->path($imagePath);
            $vehiculoImagen->imagen_order = count($imagenesSubidas) + 1;
            $vehiculoImagen->save();

            $imagenesSubidas[] = $vehiculoImagen;
        }

        return response()->json([
            'message' => 'Imágenes subidas con éxito',
            'imagenes' => $imagenesSubidas
        ], 201);
    }

    // Función para obtener las imágenes de un vehículo
    public function show($vehiculoId)
    {
        $vehiculo = Vehiculo::find($vehiculoId);
        if (!$vehiculo) {
            return response()->json(['message' => 'Vehículo no encontrado'], 404);
        }

        $imagenes = $vehiculo->imagenes->map(function($imagen) {
            return [
                'id' => $imagen->id,
                'url' => route('vehiculo.imagen', ['id' => $imagen->id]),
                'orden' => $imagen->imagen_order
            ];
        });

        return response()->json($imagenes);
    }

    // Nuevo método para mostrar la imagen por ID
    public function mostrarImagen($id)
    {
        $imagen = VehiculoImagen::find($id);
        if (!$imagen) {
            return response()->json(['message' => 'Imagen no encontrada'], 404);
        }

        // Obtener solo el nombre del archivo de la URL
        $fileName = basename($imagen->imagen_url);
        
        // Construir las rutas posibles
        $paths = [
            storage_path('app/public/' . $fileName),
            public_path('storage/' . $fileName),
            storage_path('app/public/vehiculos_imagenes/' . $fileName)
        ];

        $path = null;
        foreach ($paths as $possiblePath) {
            if (file_exists($possiblePath)) {
                $path = $possiblePath;
                break;
            }
        }

        if (!$path) {
            return response()->json([
                'message' => 'El archivo de imagen no existe',
                'detalles' => [
                    'rutas_probadas' => $paths,
                    'nombre_archivo' => $fileName,
                    'imagen_url_original' => $imagen->imagen_url
                ]
            ], 404);
        }

        try {
            $file = file_get_contents($path);
            $type = mime_content_type($path);

            return response($file)
                ->header('Content-Type', $type)
                ->header('Content-Disposition', 'inline');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al leer el archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Función para eliminar una imagen de un vehículo
    public function destroy($vehiculoId, $imagenId)
    {
        // Obtener el vehículo
        $vehiculo = Vehiculo::find($vehiculoId);
        if (!$vehiculo) {
            return response()->json(['message' => 'Vehículo no encontrado'], 404);
        }

        // Obtener la imagen a eliminar
        $vehiculoImagen = VehiculoImagen::find($imagenId);
        if (!$vehiculoImagen) {
            return response()->json(['message' => 'Imagen no encontrada'], 404);
        }

        // Eliminar la imagen del almacenamiento
        Storage::disk('public')->delete($vehiculoImagen->imagen_url);

        // Eliminar el registro de la base de datos
        $vehiculoImagen->delete();

        return response()->json(['message' => 'Imagen eliminada con éxito']);
    }

    /**
     * Muestra todas las imágenes de un vehículo específico
     */
    public function mostrarImagenesVehiculo($vehiculo_id)
    {
        $imagenes = VehiculoImagen::where('vehiculo_id', $vehiculo_id)->get();
        
        if ($imagenes->isEmpty()) {
            return response()->json(['message' => 'No se encontraron imágenes para este vehículo'], 404);
        }

        $imagenesHtml = '<html><body style="display: flex; flex-wrap: wrap; gap: 20px; padding: 20px;">';
        
        foreach ($imagenes as $imagen) {
            $imagenesHtml .= sprintf(
                '<div style="flex: 0 0 auto;"><img src="%s" style="max-width: 300px; height: auto;"></div>',
                route('vehiculo.imagen', ['id' => $imagen->id])
            );
        }
        
        $imagenesHtml .= '</body></html>';

        return response($imagenesHtml)->header('Content-Type', 'text/html');
    }

    public function primeraImagen($vehiculoId)
    {
        $vehiculo = Vehiculo::find($vehiculoId);
        if (!$vehiculo) {
            return response()->json(['message' => 'Vehículo no encontrado'], 404);
        }

        $primeraImagen = $vehiculo->imagenes()
            ->orderBy('imagen_order', 'asc')
            ->first();

        if (!$primeraImagen) {
            // Si no hay imágenes, usar la imagen por defecto
            $path = public_path('images/no-imagen.jpeg');
            
            if (!file_exists($path)) {
                return response()->json([
                    'message' => 'No hay imágenes y la imagen por defecto no existe',
                    'detalles' => [
                        'path_buscado' => $path
                    ]
                ], 404);
            }

            try {
                $file = file_get_contents($path);
                return response($file)
                    ->header('Content-Type', 'image/jpeg')
                    ->header('Content-Disposition', 'inline');
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Error al leer la imagen por defecto',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        $path = storage_path('app/public/' . $primeraImagen->imagen_url);
        
        if (!file_exists($path)) {
            return response()->json([
                'message' => 'El archivo de imagen no existe',
                'detalles' => [
                    'path_buscado' => $path,
                    'imagen_url' => $primeraImagen->imagen_url
                ]
            ], 404);
        }

        try {
            $file = file_get_contents($path);
            $type = mime_content_type($path);

            return response($file)
                ->header('Content-Type', $type)
                ->header('Content-Disposition', 'inline');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al leer el archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
