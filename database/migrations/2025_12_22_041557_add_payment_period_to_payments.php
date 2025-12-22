<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddPaymentPeriodToPayments extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            // almacenamos el primer día del mes correspondiente al pago (ej: 2025-12-01)
            $table->date('payment_period')->nullable()->after('payment_date')->index();
        });

        // Backfill: si ya hay datos en payment_date, ponemos payment_period = primer día de ese mes
        // Esto funciona en MySQL y MariaDB; si usás Postgres la función DATE_TRUNC sería distinta.
        try {
            DB::statement("UPDATE payments SET payment_period = DATE_FORMAT(payment_date, '%Y-%m-01') WHERE payment_date IS NOT NULL");
        } catch (\Throwable $e) {
            // Si la DB no soporta DATE_FORMAT en el entorno, dejarlo para un script manual.
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['payment_period']);
            $table->dropColumn('payment_period');
        });
    }
}