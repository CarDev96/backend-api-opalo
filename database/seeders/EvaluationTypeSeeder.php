<?php

namespace Database\Seeders;

use App\Models\EvaluationType;
use Illuminate\Database\Seeder;

class EvaluationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name'         => 'CERTIFICACIÓN NORMAL',
                'modality'     => 'Presencial',
                'cost'         => 2116.38,
                'includes_vat' => false,
                'description'  => 'Certificación presencial completa con prueba antidoping incluida.',
                'is_active'    => true,
            ],
            [
                'name'         => 'CERTIFICACIÓN NORMAL SIN DOPING',
                'modality'     => 'Presencial',
                'cost'         => 1811.21,
                'includes_vat' => false,
                'description'  => 'Certificación presencial sin prueba de antidoping.',
                'is_active'    => true,
            ],
            [
                'name'         => 'EVALUACIÓN REMOTA',
                'modality'     => 'Remota',
                'cost'         => 1650.00,
                'includes_vat' => false,
                'description'  => 'Evaluación psicométrica y entrevista virtual.',
                'is_active'    => true,
            ],
            [
                'name'         => 'REVALORACIÓN PRESENCIAL CON DOPING',
                'modality'     => 'Presencial',
                'cost'         => 1558.70,
                'includes_vat' => false,
                'description'  => 'Revaloración periódica presencial con examen antidoping.',
                'is_active'    => true,
            ],
            [
                'name'         => 'REVALORACIÓN PRESENCIAL',
                'modality'     => 'Presencial',
                'cost'         => 1300.00,
                'includes_vat' => false,
                'description'  => 'Revaloración periódica presencial sin antidoping.',
                'is_active'    => true,
            ],
            [
                'name'         => 'REVALORACIÓN REMOTA',
                'modality'     => 'Remota',
                'cost'         => 1300.00,
                'includes_vat' => false,
                'description'  => 'Revaloración periódica a distancia.',
                'is_active'    => true,
            ],
            [
                'name'         => 'PRUEBA DE POLÍGRAFO ESPECÍFICO/SELECCIÓN/PERMANENCIA',
                'modality'     => 'Presencial',
                'cost'         => 3562.07,
                'includes_vat' => false,
                'description'  => 'Evaluación poligráfica especializada de confiabilidad y permanencia.',
                'is_active'    => true,
            ],
            [
                'name'         => 'CERTIFICACIÓN ESPECIAL',
                'modality'     => 'Presencial',
                'cost'         => 3980.00,
                'includes_vat' => false,
                'description'  => 'Certificación integral exhaustiva para puestos de alta confianza.',
                'is_active'    => true,
            ],
            [
                'name'         => 'SOCIOECONÓMICO PLUS',
                'modality'     => 'Presencial',
                'cost'         => 650.00,
                'includes_vat' => false,
                'description'  => 'Estudio socioeconómico con visita domiciliaria e investigación de entorno.',
                'is_active'    => true,
            ],
        ];

        foreach ($types as $type) {
            EvaluationType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}
