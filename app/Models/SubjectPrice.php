<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectPrice extends Model
{
    protected $table = 'subject_prices';

    protected $fillable = [
        'subject_type_id',
        'has_teacher',
        'times_per_week',
        'price',
    ];

    protected $casts = [
        'has_teacher' => 'boolean',
        'times_per_week' => 'integer',
        'price' => 'decimal:2',
    ];

    public function subjectType()
    {
        return $this->belongsTo(SubjectType::class);
    }

    /**
     * Obtener precio.
     *
     * Lógica:
     * 1) Si existe un registro específico para subject_type_id y times_per_week => retorna ese precio.
     * 2) Si no, utiliza el precio por defecto donde subject_type_id IS NULL y has_teacher coincide (o se infiere desde subject type).
     *
     * @param int|null $subjectTypeId
     * @param int $timesPerWeek  (1..5)
     * @param bool|null $hasTeacher  (si null, el método intentará inferirlo desde SubjectType si $subjectTypeId no es null)
     * @return float|null
     */
    public static function getPriceFor(?int $subjectTypeId, int $timesPerWeek, ?bool $hasTeacher = null): ?float
    {
        if ($timesPerWeek < 1) return null;

        // Si no pasaron hasTeacher e.ins.: si subjectTypeId dado, intentar inferir
        if (is_null($hasTeacher) && $subjectTypeId) {
            $subjectType = SubjectType::find($subjectTypeId);
            if ($subjectType && isset($subjectType->has_teacher)) {
                $hasTeacher = (bool) $subjectType->has_teacher;
            }
        }

        // 1) buscar override por subject_type_id
        if ($subjectTypeId) {
            $specific = self::where('subject_type_id', $subjectTypeId)
                ->where('times_per_week', $timesPerWeek)
                ->first();
            if ($specific) {
                return (float) $specific->price;
            }
        }

        // 2) fallback al default por has_teacher
        if (!is_null($hasTeacher)) {
            $default = self::whereNull('subject_type_id')
                ->where('has_teacher', $hasTeacher)
                ->where('times_per_week', $timesPerWeek)
                ->first();
            if ($default) return (float) $default->price;
        }

        // 3) si no hay nada, null
        return null;
    }
}