<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Debt Start Month
    |--------------------------------------------------------------------------
    |
    | Define el mes a partir del cual se comienza a calcular la deuda de los
    | alumnos en cada año académico. Los meses anteriores a este no generarán
    | deuda, independientemente de cuándo se haya creado el alumno.
    |
    | Formato: número de mes (1-12)
    | Ejemplo: 3 = Marzo, 1 = Enero, 9 = Septiembre
    |
    | Valor por defecto: 3 (Marzo)
    |
    | Casos de uso:
    | - Si un alumno se crea en Enero pero el año lectivo inicia en Marzo,
    |   la deuda comenzará a contarse desde Marzo.
    | - Si un alumno se crea en Abril (después del mes de inicio), la deuda
    |   comienza desde el mes de creación del alumno.
    |
    */

    'debt_start_month' => env('DEBT_START_MONTH', 3),

    /*
    |--------------------------------------------------------------------------
    | Debt Due Day
    |--------------------------------------------------------------------------
    |
    | Define el día del mes a partir del cual se considera que un periodo
    | está vencido y se incluye en el cálculo de deuda del mes actual.
    |
    | Si el día actual es mayor a este valor, el mes en curso se considera
    | adeudado si no está totalmente pagado.
    |
    | Valor por defecto: 10
    |
    */

    'debt_due_day' => env('DEBT_DUE_DAY', 10),

];
