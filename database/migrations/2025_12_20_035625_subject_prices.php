<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_prices', function (Blueprint $table) {
            $table->id();
            // subject_type_id nullable: si es null actúa como precio por defecto para la categoría (has_teacher)
            $table->foreignId('subject_type_id')->nullable()->constrained('subject_types')->nullOnDelete();
            $table->boolean('has_teacher')->default(true)->index(); // true = con profesor, false = sin profesor
            $table->unsignedTinyInteger('times_per_week'); // 1..5
            $table->decimal('price', 12, 2);
            $table->timestamps();

            // Unicidad para evitar duplicados
            $table->unique(['subject_type_id', 'has_teacher', 'times_per_week'], 'uniq_subject_price');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_prices');
    }
};