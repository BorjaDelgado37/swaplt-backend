<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehiculoReporte extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehiculo_id',
        'pdf_url'
    ];

    // Relación con los vehículos
    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }
}
