<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Role: admin | enfermeria | alumno | profesor
            $table->string('role')->default('admin')->after('email');
            // Optional link to a teacher record (for 'profesor' role)
            $table->foreignId('teacher_id')->nullable()->after('role')
                  ->constrained('teachers')->nullOnDelete();
        });

        // Pivot: a 'alumno' user can be linked to one or more student records
        Schema::create('user_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->unique(['user_id', 'student_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_students');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropColumn(['role', 'teacher_id']);
        });
    }
};