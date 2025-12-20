<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubjectPrice;

class SubjectPriceSeeder extends Seeder
{
    public function run(): void
    {
        // Precios: con profesor (has_teacher = true)
        $withTeacher = [
            1 => 33000,
            2 => 56100,
            3 => 79200,
            4 => 99000,
            5 => 123750,
        ];

        // Precios: sin profesor (has_teacher = false) — pileta libre
        $withoutTeacher = [
            1 => 26000,
            2 => 46900,
            3 => 52200,
            4 => 55500,
            5 => 61500,
        ];

        // Inserta defaults (subject_type_id = null)
        foreach ($withTeacher as $times => $price) {
            SubjectPrice::updateOrCreate(
                ['subject_type_id' => null, 'has_teacher' => true, 'times_per_week' => $times],
                ['price' => $price]
            );
        }

        foreach ($withoutTeacher as $times => $price) {
            SubjectPrice::updateOrCreate(
                ['subject_type_id' => null, 'has_teacher' => false, 'times_per_week' => $times],
                ['price' => $price]
            );
        }

        // Opcional: overrides por subject_type_id (si quisieras un precio específico para un tipo)
        // SubjectPrice::updateOrCreate(
        //     ['subject_type_id' => 9, 'has_teacher' => false, 'times_per_week' => 1],
        //     ['price' => 26000]
        // );
    }
}