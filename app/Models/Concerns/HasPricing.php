<?php

namespace App\Models\Concerns;

use App\Models\SubjectPrice;

trait HasPricing
{
    /**
     * Obtener precio para una cantidad de clases por semana usando SubjectPrice.
     *
     * @param int $timesPerWeek
     * @return float|null
     */
    public function priceFor(int $timesPerWeek): ?float
    {
        // this should be a SubjectType model using this trait
        $subjectTypeId = $this->id ?? null;
        $hasTeacher = $this->has_teacher ?? null;

        return SubjectPrice::getPriceFor($subjectTypeId, $timesPerWeek, $hasTeacher);
    }
}