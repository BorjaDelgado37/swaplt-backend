<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class EliminarUsuariosNoVerificados extends Command
{
    // Nombre y descripción del comando
    protected $signature = 'usuarios:eliminar-no-verificados';
    protected $description = 'Eliminar usuarios que no han verificado su correo en las últimas 24 horas';

    public function __construct()
    {
        parent::__construct();
    }

    // Ejecutar el comando
    public function handle()
    {
        // Obtener los usuarios que no han verificado su correo en las últimas 24 horas
        $usuariosNoVerificados = User::whereNull('email_verified_at')
            ->where('created_at', '<', Carbon::now()->subHours(24))
            ->get();

        // Eliminar a los usuarios encontrados
        foreach ($usuariosNoVerificados as $usuario) {
            $usuario->delete();
        }

        $this->info('Tarea de eliminación completada.');
    }
}
