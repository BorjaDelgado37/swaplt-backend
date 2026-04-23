<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehiculoVisita extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehiculo_id',
        'user_id',
        'ip_address',
        'fecha_visita'
    ];

    protected $casts = [
        'fecha_visita' => 'datetime'
    ];

    // Relación con el vehículo
    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }

    // Relación con el usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 