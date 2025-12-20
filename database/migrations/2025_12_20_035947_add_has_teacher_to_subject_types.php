<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subject_types', function (Blueprint $table) {
            if (!Schema::hasColumn('subject_types', 'has_teacher')) {
                $table->boolean('has_teacher')->default(true)->after('description')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('subject_types', function (Blueprint $table) {
            if (Schema::hasColumn('subject_types', 'has_teacher')) {
                $table->dropColumn('has_teacher');
            }
        });
    }
};