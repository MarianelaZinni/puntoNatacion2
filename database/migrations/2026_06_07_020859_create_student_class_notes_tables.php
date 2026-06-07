<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_class_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title')->nullable();
            $table->text('body');
            $table->timestamps();

            $table->index(['subject_id', 'student_id']);
        });

        Schema::create('student_class_note_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_class_note_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('read_at')->useCurrent();

            $table->unique(['student_class_note_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_class_note_reads');
        Schema::dropIfExists('student_class_notes');
    }
};