<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // si tu Student extiende Model reemplazar por Model
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;
use App\Services\PriceCalculator;

class Student extends Authenticatable
{
    use HasFactory, Notifiable;

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
     * Ajustá el nombre de la tabla pivot si tu proyecto usa otro.
     */
    public function subjects()
    {
        return $this->belongsToMany(\App\Models\Subject::class, 'student_subject', 'student_id', 'subject_id')
                    ->withTimestamps();
    }

    /****************************
     * Cálculo de cuota / deuda
     ****************************/

    /**
     * Calcula la cuota mensual actual (según PriceCalculator y clases actuales).
     */
    public function currentMonthlyAmount(): float
    {
        if (! $this->relationLoaded('subjects')) {
            $this->load(['subjects' => function ($q) {
                $q->with('subjectType')->withCount('students')->orderBy('start_time');
            }]);
        }

        try {
            $summary = (new PriceCalculator())->calculate($this->subjects ?? collect());
            return isset($summary['total']) ? (float) $summary['total'] : 0.0;
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    /**
     * Suma de pagos para un periodo (float).
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
     * Determina si un periodo está totalmente abonado teniendo en cuenta
     * el monto mensual esperado ($monthlyAmount). Si $monthlyAmount es null
     * intenta calcularlo con PriceCalculator usando subjects si están cargados.
     *
     * Nota: si monthlyAmount es 0 consideramos "no pagado" (no marcamos como pagado),
     * porque no tiene sentido marcar como pagado cuando la cuota es nula.
     */
    public function isPeriodFullyPaid($period, $monthlyAmount = null): bool
    {
        if (!$period) return false;

        $paid = $this->paidAmountForPeriod($period);

        if ($monthlyAmount === null) {
            if (! $this->relationLoaded('subjects')) {
                $this->load(['subjects' => function ($q) {
                    $q->with('subjectType')->withCount('students')->orderBy('start_time');
                }]);
            }
            try {
                $summary = (new PriceCalculator())->calculate($this->subjects ?? collect());
                $monthlyAmount = $summary['total'] ?? 0.0;
            } catch (\Throwable $e) {
                // si no podemos calcular, consideramos pagado sólo si hay un pago
                return $paid > 0;
            }
        }

        // Si monthlyAmount <= 0 no marcamos pagado (evita falsos positivos)
        if ((float)$monthlyAmount <= 0) {
            return false;
        }

        return ($paid >= (float)$monthlyAmount);
    }

    /**
     * Calcula la deuda acumulada desde la creación hasta el mes actual,
     * usando el valor actual de cuota como referencia para todos los meses.
     *
     * Cambios:
     * - El período actual (mes en curso) sólo se considera "adeudado" si hoy es día > 10
     *   y no está totalmente pagado.
     * - Devuelve también 'selectable_periods' que son los periodos que pueden aparecer
     *   en el select del formulario: incluye los periodos adeudados y además siempre
     *   el periodo actual (para permitir pagar el mes actual aunque no esté vencido).
     *
     * Retorna:
     * [
     *   'debt' => float,
     *   'monthly_amount' => float,
     *   'unpaid_periods' => [ {period, paid, deficit}, ... ], // sólo los adeudados según la regla del día 10
     *   'selectable_periods' => [ {period, paid, deficit}, ... ], // para el select (unpaid + current)
     *   'next_unpaid_period' => 'YYYY-MM' | null
     * ]
     */
    public function calculateDebtFromCreationUsingCurrentMonthly(): array
    {
        if (! $this->relationLoaded('payments')) {
            $this->load('payments');
        }
        if (! $this->relationLoaded('subjects')) {
            $this->load(['subjects' => function ($q) {
                $q->with('subjectType')->withCount('students')->orderBy('start_time');
            }]);
        }

        $monthlyAmount = $this->currentMonthlyAmount();

        if ($monthlyAmount <= 0) {
            return [
                'debt' => 0.0,
                'monthly_amount' => 0.0,
                'unpaid_periods' => [],
                'selectable_periods' => [],
                'next_unpaid_period' => null,
            ];
        }

        $start = $this->created_at ? Carbon::parse($this->created_at)->startOfMonth() : Carbon::now()->startOfMonth();
        $end = Carbon::now()->startOfMonth();

        $payments = $this->payments ?? collect();

        $unpaidPeriods = [];
        $selectablePeriods = [];
        $totalDebt = 0.0;

        $includeCurrentAsDue = Carbon::now()->day > 10; // regla: mes adeudado si hoy > 10

        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $periodKey = $cursor->format('Y-m');

            // Sumar pagos cuyo payment_period coincide con este mes
            $paid = $payments->reduce(function ($carry, $p) use ($cursor) {
                $pPeriod = null;
                if (!empty($p->payment_period)) {
                    try {
                        $pPeriod = Carbon::parse($p->payment_period)->startOfMonth();
                    } catch (\Throwable $e) {
                        $pPeriod = null;
                    }
                } else {
                    try {
                        $pPeriod = Carbon::parse($p->payment_date)->startOfMonth();
                    } catch (\Throwable $e) {
                        $pPeriod = null;
                    }
                }

                if ($pPeriod && $pPeriod->format('Y-m') === $cursor->format('Y-m')) {
                    return $carry + (float) $p->amount;
                }
                return $carry;
            }, 0.0);

            $deficit = max(0.0, $monthlyAmount - $paid);

            // Si es el mes actual, solo lo marcamos adeudado (en unpaidPeriods) si includeCurrentAsDue === true
            if ($cursor->format('Y-m') === $end->format('Y-m')) {
                if ($includeCurrentAsDue && $deficit > 0) {
                    $unpaidPeriods[] = [
                        'period' => $periodKey,
                        'paid' => round($paid, 2),
                        'deficit' => round($deficit, 2),
                    ];
                    $totalDebt += $deficit;
                }
                // Para selectablePeriods incluimos igualmente el mes actual (aunque no esté vencido)
                $selectablePeriods[] = [
                    'period' => $periodKey,
                    'paid' => round($paid, 2),
                    'deficit' => round($deficit, 2),
                ];
            } else {
                // meses anteriores: se consideran adeudados si deficit > 0
                if ($deficit > 0) {
                    $unpaidPeriods[] = [
                        'period' => $periodKey,
                        'paid' => round($paid, 2),
                        'deficit' => round($deficit, 2),
                    ];
                    $totalDebt += $deficit;
                }
                // también agregamos a selectablePeriods si deficit>0 (no tiene sentido seleccionar ya pagado)
                if ($deficit > 0) {
                    $selectablePeriods[] = [
                        'period' => $periodKey,
                        'paid' => round($paid, 2),
                        'deficit' => round($deficit, 2),
                    ];
                }
            }

            $cursor->addMonth();
        }

        // Asegurarnos que selectablePeriods tenga el periodo actual primero (preseleccionado en la UI)
        usort($selectablePeriods, function ($a, $b) use ($end) {
            // poner current period (a === end) primero
            if ($a['period'] === $end->format('Y-m')) return -1;
            if ($b['period'] === $end->format('Y-m')) return 1;
            return strcmp($b['period'], $a['period']); // orden descendente por defecto
        });

        return [
            'debt' => round((float)$totalDebt, 2),
            'monthly_amount' => round((float)$monthlyAmount, 2),
            'unpaid_periods' => $unpaidPeriods,
            'selectable_periods' => $selectablePeriods,
            'next_unpaid_period' => count($unpaidPeriods) ? $unpaidPeriods[0]['period'] : null,
        ];
    }

    /**
     * Convenience: devuelve true si existe deuda desde creación.
     */
    public function hasDebtFromCreation(): bool
    {
        $res = $this->calculateDebtFromCreationUsingCurrentMonthly();
        return ($res['debt'] > 0);
    }

    /**
     * Normaliza distintos formatos de periodo a Carbon startOfMonth.
     */
    protected function normalizePeriodToCarbon($period): Carbon
    {
        if ($period instanceof Carbon) {
            return $period->copy()->startOfMonth();
        }

        if (preg_match('/^\d{4}-\d{2}$/', (string)$period)) {
            return Carbon::createFromFormat('Y-m', (string)$period)->startOfMonth();
        }

        return Carbon::parse($period)->startOfMonth();
    }
}