<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_class_notes', function (Blueprint $table) {
            // Drop the existing foreign key before changing the column
            $table->dropForeign(['student_id']);
            $table->dropIndex(['subject_id', 'student_id']);

            // Make student_id nullable (NULL = nota general para toda la clase)
            $table->foreignId('student_id')->nullable()->change();
            $table->foreign('student_id')->references('id')->on('students')->nullOnDelete();

            $table->index(['subject_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::table('student_class_notes', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropIndex(['subject_id', 'student_id']);

            $table->foreignId('student_id')->nullable(false)->change();
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();

            $table->index(['subject_id', 'student_id']);
        });
    }
};