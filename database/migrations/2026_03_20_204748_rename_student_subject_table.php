<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The original migration (2025_10_19_000338) created this pivot table with the
 * name 'Student_Subject' (mixed case). Both Eloquent model relationships
 * reference it as 'student_subject' (lowercase). On MySQL with Linux defaults
 * table names are case-sensitive, so the models were querying a table that did
 * not exist — making the students relation always return an empty collection.
 *
 * This migration renames the table to 'student_subject' to match the models.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Only rename if the mixed-case version exists and the lowercase one does not.
        // This is safe to run on fresh installs too.
        $connection = DB::getDriverName();

        if ($connection === 'sqlite') {
            // SQLite does not support RENAME TABLE via Schema::rename on older
            // versions, but does support it via raw SQL.
            // On SQLite, table names are case-insensitive so both names resolve
            // to the same table — nothing to do if that is already the case.
            // We only act if the mixed-case name is the canonical stored name.
            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='Student_Subject'");
            if (count($tables) > 0) {
                DB::statement('ALTER TABLE "Student_Subject" RENAME TO "student_subject"');
            }
        } else {
            // MySQL / MariaDB / PostgreSQL
            if (Schema::hasTable('Student_Subject') && ! Schema::hasTable('student_subject')) {
                Schema::rename('Student_Subject', 'student_subject');
            }
        }
    }

    public function down(): void
    {
        $connection = DB::getDriverName();

        if ($connection === 'sqlite') {
            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='student_subject'");
            if (count($tables) > 0) {
                DB::statement('ALTER TABLE "student_subject" RENAME TO "Student_Subject"');
            }
        } else {
            if (Schema::hasTable('student_subject') && ! Schema::hasTable('Student_Subject')) {
                Schema::rename('student_subject', 'Student_Subject');
            }
        }
    }
};