<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('medical_checkups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->date('checkup_date'); // Fecha de realización de la revisión
            $table->date('period'); // Mes/año al que corresponde (guardado como YYYY-MM-01)
            $table->boolean('approved')->default(false); // ¿Está aprobada?
            $table->text('observations')->nullable(); // Observaciones
            $table->timestamps();
            
            // Índice para búsquedas frecuentes
            $table->index(['student_id', 'period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_checkups');
    }
};