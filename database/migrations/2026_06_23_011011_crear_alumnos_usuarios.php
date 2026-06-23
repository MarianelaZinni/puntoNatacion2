<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. ESENCIAL: Inicializar las variables antes de usarlas
        $created = 0;
        $skipped = 0;
        $linked  = 0;
        $errors  = 0;

        DB::transaction(function () use (&$created, &$skipped, &$linked, &$errors) {
            
            // Usamos un array nativo por rendimiento
            $valores =[46, 61, 96, 119, 120, 169, 179, 180, 181, 188, 213, 222, 223, 231, 276, 296, 413, 468];

            // 2. OPTIMIZACIÓN: Excluir los IDs directamente en la consulta SQL para no procesarlos
            $students = DB::table('students')
                ->select('id', 'dni', 'name', 'email')
                ->whereNotIn('id', $valores) 
                ->orderBy('id')
                ->get();

            foreach ($students as $student) {
                $dni = trim($student->dni);

                if (empty($dni)) {
                    $skipped++;
                    continue;
                }

                // Buscar si ya existe un usuario con ese DNI
                $existingUser = DB::table('users')->where('dni', $dni)->first();

                if ($existingUser) {
                    $alreadyLinked = DB::table('user_students')
                        ->where('user_id', $existingUser->id)
                        ->where('student_id', $student->id)
                        ->exists();

                    if (!$alreadyLinked) {
                        DB::table('user_students')->insert([
                            'user_id'    => $existingUser->id,
                            'student_id' => $student->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $linked++;
                    }

                    $skipped++;
                    continue;
                }

                // Crear el usuario nuevo
                $userId = DB::table('users')->insertGetId([
                    'name'       => $student->name,
                    'dni'        => $dni,
                    'role'       => 'alumno',
                    'password'   => Hash::make($dni, ['rounds' => 10]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Vincular usuario con alumno
                DB::table('user_students')->insert([
                    'user_id'    => $userId,
                    'student_id' => $student->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $created++;
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Puedes dejarlo vacío o revertir los cambios si es necesario
    }
};