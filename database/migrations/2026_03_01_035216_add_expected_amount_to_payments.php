<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Payment;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Almacena el monto mensual esperado en el momento del pago
            // Esto preserva el precio histórico incluso si los precios cambian después
            $table->decimal('expected_amount', 10, 2)->nullable()->after('amount');
        });

        // Backfill: Para pagos existentes, calculamos el monto esperado
        // usando el precio actual de cada estudiante
        try {
            $payments = Payment::with('student.subjects.subjectType')->get();
            
            foreach ($payments as $payment) {
                if ($payment->student) {
                    // Calcular el precio mensual del estudiante en el momento del pago
                    // Usamos el precio actual como mejor estimación para datos históricos
                    $monthlyAmount = $payment->student->currentMonthlyAmount();
                    
                    if ($monthlyAmount > 0) {
                        $payment->expected_amount = $monthlyAmount;
                        $payment->save();
                    }
                }
            }
        } catch (\Throwable $e) {
            // Si falla el backfill, continuar (los pagos seguirán funcionando)
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('expected_amount');
        });
    }
};
