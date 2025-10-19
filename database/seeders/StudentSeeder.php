<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('es_AR');

        for ($i = 1; $i <= 50; $i++) {
            Student::create([
                'name'      => $faker->firstName . ' ' . $faker->lastName,
                'dni'       => $faker->unique()->numerify('########'),
                'email'     => $faker->unique()->safeEmail,
                'phone'     => $faker->phoneNumber,
                'address'   => $faker->address,
            ]);
        }
    }
}
