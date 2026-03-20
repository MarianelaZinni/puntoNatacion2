<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_pauses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->date('pause_period'); // stored as YYYY-MM-01 (first day of the month)
            $table->timestamps();

            // Prevent duplicate pauses for the same student/period
            $table->unique(['student_id', 'pause_period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_pauses');
    }
};