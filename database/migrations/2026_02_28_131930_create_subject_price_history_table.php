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
        Schema::create('subject_price_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_price_id')->constrained('subject_prices')->onDelete('cascade');
            
            // Copia de los datos de configuración para referencia histórica
            $table->foreignId('subject_type_id')->nullable()->constrained('subject_types')->nullOnDelete();
            $table->boolean('has_teacher')->default(true);
            $table->unsignedTinyInteger('times_per_week');
            
            // Valores históricos
            $table->decimal('old_price', 12, 2)->nullable()->comment('Precio anterior (null para registros nuevos)');
            $table->decimal('new_price', 12, 2)->comment('Nuevo precio');
            $table->timestamp('changed_at')->comment('Fecha y hora del cambio');
            
            $table->timestamps();
            
            // Índices para consultas frecuentes
            $table->index(['subject_price_id', 'changed_at']);
            $table->index('changed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_price_history');
    }
};
