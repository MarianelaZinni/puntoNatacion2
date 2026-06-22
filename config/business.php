<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Día de vencimiento de deuda
    |--------------------------------------------------------------------------
    | Día del mes a partir del cual el mes actual se considera adeudado.
    | Por ejemplo, con el valor 10: si hoy es 15 de marzo, marzo ya es una deuda.
    */
    'debt_due_day' => env('DEBT_DUE_DAY', 10),

    /*
    |--------------------------------------------------------------------------
    | Tasa de recargo
    |--------------------------------------------------------------------------
    | Porcentaje de recargo aplicado al tipo de pago "con recargo".
    | Ejemplo: 0.10 equivale al 10% de recargo sobre la cuota mensual.
    */
    'surcharge_rate' => env('PAYMENT_SURCHARGE_RATE', 0.10),

    /*
    |--------------------------------------------------------------------------
    | Fecha global de inicio de deuda del sistema
    |--------------------------------------------------------------------------
    | Mes y año (formato YYYY-MM) a partir del cual el sistema empezó a operar.
    | Ningún alumno generará deuda antes de esta fecha, independientemente de
    | cuándo fue creado o desde cuándo tiene clases asignadas.
    |
    | Ejemplo: si el natatorio abrió en marzo 2026, poner DEBT_START_DATE=2026-03
    | Un alumno creado en enero 2026 no tendrá deuda de enero ni febrero.
    | Un alumno creado en mayo 2026 tendrá deuda desde mayo 2026.
    | (siempre se toma el máximo entre esta fecha y la fecha del alumno)
    |
    | Si se deja vacío, no hay restricción global y cada alumno genera
    | deuda desde su fecha de creación o active_from.
    */
    'debt_start_date' => env('DEBT_START_DATE', ''),
];