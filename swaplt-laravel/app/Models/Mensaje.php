<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mensaje extends Model
{
    use HasFactory;

    protected $fillable = ['emisor_id', 'receptor_id', 'contenido', 'leido'];

    // Relación con el usuario que envió el mensaje
    public function emisor()
    {
        return $this->belongsTo(User::class, 'emisor_id');
    }

    // Relación con el usuario que recibió el mensaje
    public function receptor()
    {
        return $this->belongsTo(User::class, 'receptor_id');
    }
}
