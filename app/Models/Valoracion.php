<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Valoracion extends Model
{
    use HasFactory;

    protected $table = 'valoraciones';

    protected $fillable = [
        'emisor_id',
        'receptor_id',
        'valor',
        'comentario',
    ];

    // Usuario que envía la valoración
    public function emisor()
    {
        return $this->belongsTo(User::class, 'emisor_id');
    }

    // Usuario que recibe la valoración
    public function receptor()
    {
        return $this->belongsTo(User::class, 'receptor_id');
    }
} 