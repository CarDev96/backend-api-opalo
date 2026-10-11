<?php

namespace Database\Seeders;

use App\Models\EvaluationType;
use Illuminate\Database\Seeder;

class EvaluationTypeSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tipos de Evaluación Presenciales
        $presenciales = [
            'Sin doping',
            'Normal',
            'Revalorización',
            'Polígrafo específico',
            'Polígrafo de permanencia',
            'Polígrafo de selección',
        ];

        foreach ($presenciales as $nombre) {
            EvaluationType::firstOrCreate(
                ['name' => $nombre, 'modality' => 'presencial'],
                ['is_active' => true]
            );
        }

        // 2. Evaluaciones Remotas
        EvaluationType::firstOrCreate(
            ['name' => 'Evaluación Remota', 'modality' => 'remota'],
            [
                'description' => 'Aplicación de batería psicométrica y entrevista virtual',
                'is_active'   => true,
            ]
        );
    }
}
