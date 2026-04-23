<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre'
    ];

    // Relación con los vehículos
    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class);
    }
}
