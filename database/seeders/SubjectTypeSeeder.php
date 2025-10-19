<?php

namespace Database\Seeders;

use App\Models\SubjectType;
use Illuminate\Database\Seeder;

class SubjectTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $subjectTypes = [
            ['description' => 'Pileta Libre', 'value' => 150.00],
            ['description' => 'Natacion Adultos', 'value' => 200.00],
            ['description' => 'Gim', 'value' => 200.00],
            ['description' => 'Bebes', 'value' => 180.00],
            ['description' => 'Familiar', 'value' => 170.00],
            ['description' => 'Ambientacion', 'value' => 170.00],
            ['description' => 'Principiantes 1', 'value' => 170.00],
            ['description' => 'Principiantes 2', 'value' => 170.00]
        ];

        foreach ($subjectTypes as $type) {
            SubjectType::create($type);
        }
    }
}

