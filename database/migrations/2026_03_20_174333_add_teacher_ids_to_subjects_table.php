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
        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('titular_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->foreignId('suplente_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropForeign(['titular_teacher_id']);
            $table->dropForeign(['suplente_teacher_id']);
            $table->dropColumn(['titular_teacher_id', 'suplente_teacher_id']);
        });
    }
};
