<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix: the attendances table had onDelete('cascade') on both subject_id and
 * student_id.  This meant that deleting a subject or a student would silently
 * wipe out ALL of its historical attendance records – the root cause of the
 * "attendance lists disappearing" bug reported by users.
 *
 * Changing both constraints to RESTRICT makes MySQL refuse the DELETE and
 * returns a QueryException that the controllers can catch and surface as a
 * readable error message instead of silently destroying data.
 */
return new class extends Migration
{
    public function up(): void
    {
        // MySQL / MariaDB require dropping the old FK, then re-adding with the
        // new action.  Laravel's Schema builder handles this cleanly.
        Schema::table('attendances', function (Blueprint $table) {
            // Drop the existing constraints by their conventional names.
            // Laravel names them: {table}_{column}_foreign
            $table->dropForeign(['subject_id']);
            $table->dropForeign(['student_id']);

            // Re-add them with RESTRICT so that deletions are blocked when
            // attendance records exist.
            $table->foreign('subject_id')
                  ->references('id')->on('subjects')
                  ->onDelete('restrict');

            $table->foreign('student_id')
                  ->references('id')->on('students')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropForeign(['student_id']);

            // Restore original (cascade) behaviour on rollback.
            $table->foreign('subject_id')
                  ->references('id')->on('subjects')
                  ->onDelete('cascade');

            $table->foreign('student_id')
                  ->references('id')->on('students')
                  ->onDelete('cascade');
        });
    }
};