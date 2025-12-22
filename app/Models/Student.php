<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // si tu Student extiende Model, reemplaza por Model
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class Student extends Authenticatable
{
    use HasFactory, Notifiable;

    // Ajustá $fillable a los campos reales de tu tabla students
    protected $fillable = [
        'dni',
        'name',
        'email',
        'address',
        'phone',
        // ...
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**************
     * Relaciones *
     **************/
    public function payments()
    {
        return $this->hasMany(\App\Models\Payment::class);
    }

    /**
     * Relación many-to-many con subjects.
     * IMPORTANTE: especificamos explícitamente el nombre de la tabla pivot
     * real en la DB: 'student_subject'.
     */
    public function subjects()
    {
        return $this->belongsToMany(\App\Models\Subject::class, 'student_subject', 'student_id', 'subject_id')
                    ->withTimestamps();
    }

    /***********************
     * Helpers de pagos / periodo
     ***********************/

    /**
     * Devuelve true si el alumno tiene (al menos) pagos en el periodo dado.
     * $period puede ser:
     * - instancia Carbon
     * - string 'YYYY-MM'
     * - string 'YYYY-MM-DD' (se tomará el mes)
     */
    public function hasPaidPeriod($period): bool
    {
        if (!$period) return false;

        $c = $this->normalizePeriodToCarbon($period);
        return $this->payments()
            ->whereYear('payment_period', $c->year)
            ->whereMonth('payment_period', $c->month)
            ->exists();
    }

    /**
     * Devuelve la suma de pagos para un periodo (float).
     */
    public function paidAmountForPeriod($period): float
    {
        if (!$period) return 0.0;
        $c = $this->normalizePeriodToCarbon($period);
        return (float) $this->payments()
            ->whereYear('payment_period', $c->year)
            ->whereMonth('payment_period', $c->month)
            ->sum('amount');
    }

    /**
     * Retorna colección de pagos para un periodo.
     */
    public function paymentsForPeriod($period)
    {
        if (!$period) return collect();
        $c = $this->normalizePeriodToCarbon($period);
        return $this->payments()
            ->whereYear('payment_period', $c->year)
            ->whereMonth('payment_period', $c->month)
            ->get();
    }

    /**
     * Retorna array de periodos pagados en formato 'YYYY-MM'
     */
    public function paidPeriods(): array
    {
        return $this->payments()->get()->map(function ($p) {
            if (!$p->payment_period) return null;
            return Carbon::parse($p->payment_period)->format('Y-m');
        })->filter()->unique()->values()->toArray();
    }

    /**
     * Determina si un periodo está totalmente abonado teniendo en cuenta
     * el monto mensual esperado ($monthlyAmount). Si $monthlyAmount es null
     * intenta calcularlo con PriceCalculator usando subjects si están cargados.
     *
     * Si preferís política "un pago por mes" en lugar de suma >= monthlyAmount,
     * adaptá esta función.
     */
    public function isPeriodFullyPaid($period, $monthlyAmount = null): bool
    {
        if (!$period) return false;

        $paid = $this->paidAmountForPeriod($period);

        if ($monthlyAmount === null) {
            // intentamos calcular con PriceCalculator si subjects están cargados
            if ($this->relationLoaded('subjects')) {
                $summary = (new \App\Services\PriceCalculator())->calculate($this->subjects);
                $monthlyAmount = $summary['total'] ?? 0.0;
            } else {
                // si no podemos calcular el monto, consideramos pagado sólo si hay algún pago
                return $paid > 0;
            }
        }

        // si la suma de pagos >= monto mensual lo consideramos pagado
        return ($paid >= (float)$monthlyAmount);
    }

    /**
     * Calcula la deuda para un periodo concreto: monto mensual (PriceCalculator)
     * menos pagos realizados en ese periodo; no baja de 0.
     */
    public function debtForPeriod($period, $monthlyAmount = null): float
    {
        if (!$period) return 0.0;

        if ($monthlyAmount === null) {
            if ($this->relationLoaded('subjects')) {
                $summary = (new \App\Services\PriceCalculator())->calculate($this->subjects);
                $monthlyAmount = $summary['total'] ?? 0.0;
            } else {
                $monthlyAmount = 0.0;
            }
        }

        $paid = $this->paidAmountForPeriod($period);
        $debt = max(0.0, (float)$monthlyAmount - (float)$paid);
        return $debt;
    }

    /**
     * Normalize various period inputs to a Carbon at startOfMonth.
     */
    protected function normalizePeriodToCarbon($period): Carbon
    {
        if ($period instanceof Carbon) {
            return $period->copy()->startOfMonth();
        }

        // accept 'YYYY-MM' or 'YYYY-MM-DD' or a date string
        if (preg_match('/^\d{4}-\d{2}$/', $period)) {
            return Carbon::createFromFormat('Y-m', $period)->startOfMonth();
        }

        return Carbon::parse($period)->startOfMonth();
    }
}