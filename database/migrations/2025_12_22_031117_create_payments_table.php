<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');

            // Referencia a payment_methods
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();

            $table->decimal('amount', 10, 2); // Monto pagado
            $table->date('payment_date'); // Fecha de pago
            $table->text('notes')->nullable(); // notas opcionales
            $table->timestamps(); // created_at / updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}