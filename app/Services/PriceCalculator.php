<?php

namespace App\Services;

use App\Models\SubjectPrice;
use Illuminate\Support\Collection;

/**
 * Servicio para calcular el precio de inscripción según las clases
 * seleccionadas por semana.
 *
 * Nueva regla:
 * - Si el alumno tiene al menos UNA clase con profesor, se cuenta el total de clases
 *   (con y sin profesor), se limita a 5 y se aplica la tarifa de "con profesor" para el total.
 * - Si no tiene clases con profesor (solo sin profesor), se cuenta la cantidad de clases
 *   sin profesor (tope 5) y se aplica la tarifa "sin profesor".
 *
 * Retorna un array con keys compatibles con el frontend:
 *  - teacher_count, no_teacher_count
 *  - teacher_price (float|null), no_teacher_price (float|null)
 *  - applied_category ('teacher'|'no_teacher')
 *  - applied_count (int)
 *  - total (float|null)
 *  - details (array)
 */
class PriceCalculator
{
    /**
     * Calcular precio.
     *
     * @param \Illuminate\Support\Collection|array $subjects Collection/array de Subject models.
     *        Cada subject debe exponer $subject->subjectType and $subject->subjectType->has_teacher (bool)
     * @return array
     */
    public function calculate($subjects): array
    {
        $collection = $subjects instanceof Collection ? $subjects : collect($subjects);

        // Contar por categoría
        $teacherCount = 0;
        $noTeacherCount = 0;

        foreach ($collection as $subject) {
            $hasTeacher = null;
            if (isset($subject->subjectType) && isset($subject->subjectType->has_teacher)) {
                $hasTeacher = (bool) $subject->subjectType->has_teacher;
            } elseif (isset($subject->has_teacher)) {
                $hasTeacher = (bool) $subject->has_teacher;
            } else {
                // si no puede determinar, asumimos con profesor por defecto
                $hasTeacher = true;
            }

            if ($hasTeacher) {
                $teacherCount++;
            } else {
                $noTeacherCount++;
            }
        }

        $details = [];

        // Aplico la regla solicitada
        if ($teacherCount > 0) {
            // hay al menos una con profe => usamos tarifa con profe sobre el total (tope 5)
            $appliedCategory = 'teacher';
            $appliedCount = min($teacherCount + $noTeacherCount, 5);
            $appliedPrice = SubjectPrice::getPriceFor(null, $appliedCount, true);
            if (is_null($appliedPrice)) {
                $details[] = "Falta precio configurado para clases con profesor con {$appliedCount} vez/veces por semana.";
            }

            // Llenamos campos compatibles:
            $teacherPrice = $appliedPrice;
            $noTeacherPrice = null;
            $total = $appliedPrice !== null ? (float) $appliedPrice : null;
        } else {
            // solo sin profe => aplicamos tarifa sin profe sobre noTeacherCount (tope 5)
            $appliedCategory = 'no_teacher';
            $appliedCount = min($noTeacherCount, 5);
            $appliedPrice = SubjectPrice::getPriceFor(null, $appliedCount, false);
            if (is_null($appliedPrice)) {
                $details[] = "Falta precio configurado para clases sin profesor con {$appliedCount} vez/veces por semana.";
            }

            $teacherPrice = null;
            $noTeacherPrice = $appliedPrice;
            $total = $appliedPrice !== null ? (float) $appliedPrice : null;
        }

        return [
            'teacher_count' => $teacherCount,
            'no_teacher_count' => $noTeacherCount,
            'teacher_price' => $teacherPrice,
            'no_teacher_price' => $noTeacherPrice,
            'applied_category' => $appliedCategory,
            'applied_count' => $appliedCount,
            'total' => $total,
            'details' => $details,
        ];
    }
}