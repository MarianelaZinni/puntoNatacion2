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
];
