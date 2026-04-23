<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'categoria_id',
        'marca',
        'modelo',
        'precio',
        'anio',
        'estado',
        'categoria_id',
        'transmision',
        'tipo_combustible',
        'kilometraje',
        'fuerza',
        'capacidad_motor',
        'color',
        'fecha_publicacion',
        'ubicacion',
        'matricula',
        'numero_serie',
        'numero_puertas',
        'descripcion',
        'vehiculo_robado',
        'vehiculo_libre_accidentes'
    ];

    // Relación con la tabla users (un usuario puede tener varios vehículos)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con el modelo Categoria
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    // Relación con las imágenes de los vehículos
    public function imagenes()
    {
        return $this->hasMany(VehiculoImagen::class, 'vehiculo_id');
    }

    // Relación con los reportes del vehículo
    public function reportes()
    {
        return $this->hasMany(VehiculoReporte::class);
    }

    // Relación con los mensajes
    public function mensajes()
    {
        return $this->hasMany(Mensaje::class);
    }

    // Relación con los favoritos
    public function favoritos()
    {
        return $this->belongsToMany(User::class, 'favoritos');
    }

    // Relación con las visitas
    public function visitas()
    {
        return $this->hasMany(VehiculoVisita::class);
    }

    // Método para obtener estadísticas de visitas
    public function getEstadisticasVisitas($dias = 30)
    {
        return $this->visitas()
            ->selectRaw('DATE(fecha_visita) as fecha, COUNT(*) as total_visitas')
            ->where('fecha_visita', '>=', now()->subDays($dias))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();
    }
}
