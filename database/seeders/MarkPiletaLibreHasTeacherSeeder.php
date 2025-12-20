<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubjectType;
use Illuminate\Support\Str;

class MarkPiletaLibreHasTeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder will set has_teacher = false for subject types that look like
     * "pileta libre" (case-insensitive match against `value` or partial match
     * against `description`). It is intentionally permissive to catch variants
     * like "Pileta Libre", "pileta", "Pileta - libre", etc.
     *
     * Run with:
     *  php artisan db:seed --class=MarkPiletaLibreHasTeacherSeeder
     */
    public function run(): void
    {
        // Build a query that tries several case-insensitive matches
        $query = SubjectType::query();

        // First try exact matches against common variants (case-insensitive)
        $exactVariants = [
            'pileta libre',
            'pileta',
            'pileta-libre',
        ];

        $queryExact = SubjectType::where(function ($q) use ($exactVariants) {
            foreach ($exactVariants as $variant) {
                $q->orWhereRaw('LOWER(COALESCE(value, \'\')) = ?', [Str::lower($variant)]);
            }
        });

        // Also include partial match on description containing "pileta"
        $queryPartial = SubjectType::whereRaw('LOWER(COALESCE(description, \'\')) LIKE ?', ['%pileta%']);

        // Combine both result sets' IDs to update
        $idsExact = $queryExact->pluck('id')->toArray();
        $idsPartial = $queryPartial->pluck('id')->toArray();

        $ids = array_values(array_unique(array_merge($idsExact, $idsPartial)));

        if (count($ids) === 0) {
            if ($this->command) {
                $this->command->warn("MarkPiletaLibreHasTeacherSeeder: No subject_type matched 'pileta libre' or description like '%pileta%'.");
            }
            return;
        }

        $updated = SubjectType::whereIn('id', $ids)->update(['has_teacher' => false]);

        if ($this->command) {
            $this->command->info("MarkPiletaLibreHasTeacherSeeder: Updated {$updated} subject_type(s) to has_teacher = false (IDs: " . implode(', ', $ids) . ").");
        }
    }
}