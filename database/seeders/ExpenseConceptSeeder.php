<?php

namespace Database\Seeders;

use App\Models\ExpenseConcept;
use Illuminate\Database\Seeder;

class ExpenseConceptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $concepts = [
            [
                'name' => 'Alimentación',
                'description' => 'Gastos relacionados con comidas, desayunos, almuerzos, cenas y bebidas durante el viaje.',
                'is_unmanaged' => false,
            ],
            [
                'name' => 'Hospedaje',
                'description' => 'Gastos de hotel, hostal, apartamento u otro tipo de alojamiento durante el viaje.',
                'is_unmanaged' => false,
            ],
            [
                'name' => 'Transporte',
                'description' => 'Gastos de transporte local, taxis, autobuses, metro, tren o cualquier medio de movilización.',
                'is_unmanaged' => false,
            ],
            [
                'name' => 'Combustible',
                'description' => 'Gastos de gasolina, diesel u otro combustible para vehículos utilizados durante el viaje.',
                'is_unmanaged' => false,
            ],
            [
                'name' => 'Comunicaciones',
                'description' => 'Gastos de llamadas telefónicas, internet, datos móviles necesarios para el trabajo durante el viaje.',
                'is_unmanaged' => false,
            ],
            [
                'name' => 'Materiales y Suministros',
                'description' => 'Gastos de materiales de oficina, suministros o herramientas necesarias para el trabajo.',
                'is_unmanaged' => false,
            ],
            [
                'name' => 'Otros Gastos',
                'description' => 'Gastos diversos que no encajan en las categorías anteriores pero son necesarios para el viaje.',
                'is_unmanaged' => true,
            ],
        ];

        foreach ($concepts as $concept) {
            ExpenseConcept::firstOrCreate(
                ['name' => $concept['name']],
                $concept
            );
        }
    }
}
