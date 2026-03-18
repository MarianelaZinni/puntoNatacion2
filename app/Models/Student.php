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
        'observations',
        'birth_date'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
    'birth_date' => 'date'
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
 * Calcula la deuda del alumno desde su creación, usando el monto mensual ACTUAL.
 * Respeta la fecha absoluta de inicio de deuda configurada en el sistema.
 *
 * Retorna: [
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

    // Fecha de creación del alumno
    $creationDate = $this->created_at ? Carbon::parse($this->created_at)->startOfMonth() : Carbon::now()->startOfMonth();
    
    // Fecha absoluta de inicio de deuda del sistema (configurada en .env)
    $systemDebtStartDate = $this->getSystemDebtStartDate();
    
    // Calcular el mes de inicio efectivo de la deuda
    // Usar la fecha más reciente entre: creación del alumno y fecha de inicio del sistema
    $start = $this->calculateEffectiveDebtStartDate($creationDate, $systemDebtStartDate);
    
    $end = Carbon::now()->startOfMonth();

    $payments = $this->payments ?? collect();

    $unpaidPeriods = [];
    $selectablePeriods = [];
    $totalDebt = 0.0;

    // Día de vencimiento configurado (por defecto: 10)
    $debtDueDay = config('business.debt_due_day', 10);
    $includeCurrentAsDue = Carbon::now()->day > $debtDueDay; // regla: mes adeudado si hoy > día configurado

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
            // También van a selectablePeriods todos los meses adeudados
            $selectablePeriods[] = [
                'period' => $periodKey,
                'paid' => round($paid, 2),
                'deficit' => round($deficit, 2),
            ];
        }

        $cursor->addMonth();
    }

    // Invertir listas para orden descendente (más recientes primero)
    usort($unpaidPeriods, function ($a, $b) {
        return strcmp($b['period'], $a['period']);
    });
    usort($selectablePeriods, function ($a, $b) {
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
 * Obtiene la fecha absoluta de inicio de deuda del sistema desde la configuración.
 * Si no está configurada, retorna null.
 *
 * @return Carbon|null
 */
protected function getSystemDebtStartDate(): ?Carbon
{
    $debtStartDateConfig = '2026-03';
    
    if (empty($debtStartDateConfig)) {
        return null;
    }
    
    try {
        // Espera formato 'YYYY-MM'
        return Carbon::createFromFormat('Y-m', $debtStartDateConfig)->startOfMonth();
    } catch (\Throwable $e) {
        // Si hay error en el formato, retorna null (sin restricción)
        return null;
    }
}

/**
 * Calcula la fecha efectiva desde la cual se debe empezar a contar la deuda.
 *
 * Lógica:
 * - Si existe una fecha de inicio del sistema (DEBT_START_DATE), se usa la más reciente
 *   entre la fecha de creación del alumno y la fecha de inicio del sistema.
 * - Si no existe fecha de inicio del sistema, se usa la fecha de creación del alumno.
 *
 * Ejemplo con DEBT_START_DATE = '2024-03' (Marzo 2024):
 * - Alumno creado en Enero 2024 → deuda desde Marzo 2024
 * - Alumno creado en Abril 2024 → deuda desde Abril 2024
 * - Alumno creado en Diciembre 2023 → deuda desde Marzo 2024
 * - Alumno creado en Junio 2024 → deuda desde Junio 2024
 *
 * @param Carbon $creationDate Fecha de creación del alumno
 * @param Carbon|null $systemDebtStartDate Fecha de inicio del sistema (puede ser null)
 * @return Carbon Fecha efectiva de inicio de deuda
 */
protected function calculateEffectiveDebtStartDate(Carbon $creationDate, ?Carbon $systemDebtStartDate): Carbon
{
    // Si no hay fecha de inicio del sistema configurada, usar fecha de creación
    if ($systemDebtStartDate === null) {
        return $creationDate->copy();
    }
    
    // Si el alumno se creó antes de la fecha de inicio del sistema,
    // la deuda comienza en la fecha de inicio del sistema
    if ($creationDate->lt($systemDebtStartDate)) {
        return $systemDebtStartDate->copy();
    }
    
    // Si el alumno se creó después de la fecha de inicio del sistema,
    // la deuda comienza desde la fecha de creación
    return $creationDate->copy();
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

    /**
    * Calcula la edad del estudiante basándose en su fecha de nacimiento.
    * Retorna null si no tiene fecha de nacimiento.
    */
    public function getAgeAttribute(): ?int
    {
        if (!$this->birth_date) {
            return null;
        }
        return Carbon::parse($this->birth_date)->age;
    }

    /**
     * Revisiones médicas del alumno.
     */
    public function medicalCheckups()
    {
        return $this->hasMany(MedicalCheckup::class);
    }
}