<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserBlock;
use App\Models\User;

class UserBlockSeeder extends Seeder
{
    public function run(): void
    {
        // Asegurarse de que existan usuarios para los bloqueos
        $usuarios = User::all();
        
        if ($usuarios->count() < 5) {
            $this->command->info('Se necesitan al menos 5 usuarios para crear los bloqueos de ejemplo.');
            return;
        }

        // Datos de ejemplo para los bloqueos
        $bloqueos = [
            [
                'blocker_id' => $usuarios[0]->id, // Primer usuario
                'blocked_id' => $usuarios[1]->id, // Segundo usuario
                'razon' => 'Comportamiento inapropiado en mensajes'
            ],
            [
                'blocker_id' => $usuarios[1]->id,
                'blocked_id' => $usuarios[2]->id,
                'razon' => 'Spam en comentarios'
            ],
            [
                'blocker_id' => $usuarios[2]->id,
                'blocked_id' => $usuarios[3]->id,
                'razon' => 'Publicación de contenido inapropiado'
            ],
            [
                'blocker_id' => $usuarios[3]->id,
                'blocked_id' => $usuarios[4]->id,
                'razon' => 'Incumplimiento de normas de la comunidad'
            ],
            [
                'blocker_id' => $usuarios[4]->id,
                'blocked_id' => $usuarios[0]->id,
                'razon' => 'Acoso en mensajes privados'
            ],
            [
                'blocker_id' => $usuarios[0]->id,
                'blocked_id' => $usuarios[2]->id,
                'razon' => 'Publicación de información falsa'
            ],
            [
                'blocker_id' => $usuarios[1]->id,
                'blocked_id' => $usuarios[3]->id,
                'razon' => 'Comportamiento agresivo'
            ]
        ];

        // Insertar los bloqueos
        foreach ($bloqueos as $bloqueo) {
            UserBlock::create($bloqueo);
        }

        $this->command->info('Se han creado ' . count($bloqueos) . ' bloqueos de ejemplo.');
    }
} 