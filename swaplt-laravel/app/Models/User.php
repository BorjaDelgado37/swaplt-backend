<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
        'verification_code',
        'email_verified_at',
        'reset_token',
        'reset_token_expires_at',
        'google_id',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function markEmailAsVerified()
    {
        $this->email_verified_at = now();
        $this->verification_code = null;
        $this->save();
    }

    public function getJWTIdentifier()
    {
        return $this->getKey(); // Esto devuelve el ID del usuario
    }

    public function getJWTCustomClaims()
    {
        return []; // Aquí puedes agregar cualquier dato adicional que desees incluir en el JWT
    }

    // Valoraciones recibidas
    public function valoracionesRecibidas()
    {
        return $this->hasMany(\App\Models\Valoracion::class, 'receptor_id');
    }

    // Valoraciones enviadas
    public function valoracionesEnviadas()
    {
        return $this->hasMany(\App\Models\Valoracion::class, 'emisor_id');
    }

    // Relaciones para bloqueos
    public function bloqueosRealizados(): HasMany
    {
        return $this->hasMany(UserBlock::class, 'blocker_id');
    }

    public function bloqueosRecibidos(): HasMany
    {
        return $this->hasMany(UserBlock::class, 'blocked_id');
    }

    // Métodos para manejar bloqueos
    public function bloquearUsuario(User $usuario, ?string $razon = null): UserBlock
    {
        return $this->bloqueosRealizados()->create([
            'blocked_id' => $usuario->id,
            'razon' => $razon
        ]);
    }

    public function desbloquearUsuario(User $usuario): bool
    {
        return $this->bloqueosRealizados()
            ->where('blocked_id', $usuario->id)
            ->delete();
    }

    public function estaBloqueadoPor(User $usuario): bool
    {
        return $this->bloqueosRecibidos()
            ->where('blocker_id', $usuario->id)
            ->exists();
    }

    public function haBloqueadoA(User $usuario): bool
    {
        return $this->bloqueosRealizados()
            ->where('blocked_id', $usuario->id)
            ->exists();
    }

    public function usuariosBloqueados()
    {
        return User::whereIn('id', $this->bloqueosRealizados()->pluck('blocked_id'));
    }

    public function usuariosQueMeBloquearon()
    {
        return User::whereIn('id', $this->bloqueosRecibidos()->pluck('blocker_id'));
    }
}
