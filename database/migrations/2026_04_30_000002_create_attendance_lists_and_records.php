<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Splits the flat `attendances` table into two:
 *
 *  attendance_lists   — one row per (subject, date) session
 *  attendance_records — one row per (list, student) with the present flag
 *
 * Data from the old `attendances` table is migrated automatically.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. attendance_lists ──────────────────────────────────────────────
        Schema::create('attendance_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')
                  ->constrained()
                  ->onDelete('restrict');
            $table->date('date');
            $table->timestamps();

            $table->unique(['subject_id', 'date']);
        });

        // ── 2. attendance_records ────────────────────────────────────────────
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_list_id')
                  ->constrained('attendance_lists')
                  ->onDelete('cascade');
            $table->foreignId('student_id')
                  ->constrained()
                  ->onDelete('restrict');
            $table->boolean('present')->default(true);
            $table->string('observations', 500)->nullable();
            $table->timestamps();

            $table->unique(['attendance_list_id', 'student_id']);
        });

        // ── 3. Migrate existing data ─────────────────────────────────────────
        if (Schema::hasTable('attendances')) {
            // Insert one attendance_list per unique (subject_id, date) pair.
            DB::statement("
                INSERT INTO attendance_lists (subject_id, date, created_at, updated_at)
                SELECT subject_id, date, MIN(created_at), MAX(updated_at)
                FROM attendances
                GROUP BY subject_id, date
            ");

            // Insert attendance_records referencing the freshly created lists.
            DB::statement("
                INSERT INTO attendance_records
                    (attendance_list_id, student_id, present, observations, created_at, updated_at)
                SELECT al.id, a.student_id, a.present, a.observations, a.created_at, a.updated_at
                FROM attendances a
                JOIN attendance_lists al ON al.subject_id = a.subject_id AND al.date = a.date
            ");

            // ── 4. Drop old table ─────────────────────────────────────────────
            Schema::drop('attendances');
        }
    }

    public function down(): void
    {
        // Restore the original attendances table (with CASCADE, matching the
        // original migration before the fix-migration changed it to RESTRICT).
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->boolean('present')->default(true);
            $table->string('observations', 500)->nullable();
            $table->timestamps();

            $table->unique(['subject_id', 'student_id', 'date']);
        });

        // Move data back.
        DB::statement("
            INSERT INTO attendances
                (subject_id, student_id, date, present, observations, created_at, updated_at)
            SELECT al.subject_id, ar.student_id, al.date, ar.present, ar.observations,
                   ar.created_at, ar.updated_at
            FROM attendance_records ar
            JOIN attendance_lists al ON al.id = ar.attendance_list_id
        ");

        Schema::drop('attendance_records');
        Schema::drop('attendance_lists');
    }
};
