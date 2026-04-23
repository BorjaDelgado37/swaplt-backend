<?php

// app/Models/VehiculoImagen.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehiculoImagen extends Model
{
    use HasFactory;

    protected $table = 'vehiculos_imagenes'; // Definir la tabla explícitamente

    protected $attributes = [
        'imagen_order' => 1, // Valor por defecto para imagen_order
    ];

    protected $fillable = [
        'vehiculo_id',
        'imagen_url',
        'imagen_path',
        'imagen_order',
    ];

    // Relación con el modelo Vehiculo
    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id');
    }
}
