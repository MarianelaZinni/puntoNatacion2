<?php

namespace App\Services;

use App\Models\SubjectPrice;
use Illuminate\Support\Collection;

/**
 * Servicio para calcular el precio de inscripción según las clases
 * seleccionadas por semana.
 *
 * Reglas:
 * - Se cuentan cuántas clases por semana son CON PROFESOR y cuántas SIN.
 * - Para cada categoría se busca el precio por la cantidad de veces por semana (1..5).
 * - Si faltase una fila de precio para una categoría y cantidad, devuelve null en esa parte
 *   y agrega una nota en details.
 */
class PriceCalculator
{
    /**
     * Calcular precio.
     *
     * @param \Illuminate\Support\Collection|array $subjects Collection/array de Subject models.
     *        Cada subject debe exponer $subject->subjectType and $subject->subjectType->has_teacher (bool)
     * @return array [
     *    'teacher_count' => int,
     *    'no_teacher_count' => int,
     *    'teacher_price' => float|null,
     *    'no_teacher_price' => float|null,
     *    'total' => float|null,
     *    'details' => array (mensajes)
     * ]
     */
    public function calculate($subjects): array
    {
        $collection = $subjects instanceof Collection ? $subjects : collect($subjects);

        // Contar por categoría
        $teacherCount = 0;
        $noTeacherCount = 0;

        foreach ($collection as $subject) {
            $hasTeacher = null;
            // Preferir el flag en subjectType si existe
            if (isset($subject->subjectType) && isset($subject->subjectType->has_teacher)) {
                $hasTeacher = (bool) $subject->subjectType->has_teacher;
            } elseif (isset($subject->has_teacher)) {
                $hasTeacher = (bool) $subject->has_teacher;
            } else {
                // si no puede determinar, asumimos con profesor (opcional)
                $hasTeacher = true;
            }

            if ($hasTeacher) $teacherCount++;
            else $noTeacherCount++;
        }

        $details = [];

        $teacherPrice = null;
        if ($teacherCount > 0) {
            // usamos subject_type_id = null para tomar los defaults por has_teacher
            $teacherPrice = SubjectPrice::getPriceFor(null, $teacherCount, true);
            if (is_null($teacherPrice)) {
                $details[] = "Falta precio configurado para clases con profesor con {$teacherCount} vez/veces por semana.";
            }
        }

        $noTeacherPrice = null;
        if ($noTeacherCount > 0) {
            $noTeacherPrice = SubjectPrice::getPriceFor(null, $noTeacherCount, false);
            if (is_null($noTeacherPrice)) {
                $details[] = "Falta precio configurado para clases sin profesor con {$noTeacherCount} vez/veces por semana.";
            }
        }

        $total = null;
        if (!is_null($teacherPrice) || !is_null($noTeacherPrice)) {
            $total = (float) (($teacherPrice ?? 0) + ($noTeacherPrice ?? 0));
        }

        return [
            'teacher_count' => $teacherCount,
            'no_teacher_count' => $noTeacherCount,
            'teacher_price' => $teacherPrice,
            'no_teacher_price' => $noTeacherPrice,
            'total' => $total,
            'details' => $details,
        ];
    }
}