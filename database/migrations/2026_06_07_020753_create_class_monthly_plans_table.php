<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_monthly_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->date('month');
            $table->text('content');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['subject_id', 'month']);
            $table->index('month');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_monthly_plans');
    }
};
