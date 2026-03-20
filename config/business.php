<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Día de vencimiento de cuotas
    |--------------------------------------------------------------------------
    | Día del mes a partir del cual se considera que la cuota del mes corriente
    | está vencida y se agrega a la deuda del alumno.
    */
    'debt_due_day' => env('DEBT_DUE_DAY', 10),

    /*
    |--------------------------------------------------------------------------
    | Porcentaje de recargo por pago tardío
    |--------------------------------------------------------------------------
    | Porcentaje adicional que se aplica al monto normal de la cuota cuando el
    | alumno elige la opción "pago con recargo". Ejemplo: 10 = 10%.
    */
    'payment_surcharge_percentage' => env('PAYMENT_SURCHARGE_PERCENTAGE', 10),
];
