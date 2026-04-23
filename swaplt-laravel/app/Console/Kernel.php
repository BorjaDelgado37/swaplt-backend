<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Definir las tareas programadas para el proyecto.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Ejecutar el comando a diario
        $schedule->command('usuarios:eliminar-no-verificados')->daily();
    }


    /**
     * Configurar los comandos de la consola.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    protected $commands = [
        Commands\EliminarUsuariosNoVerificados::class,
    ];

}
