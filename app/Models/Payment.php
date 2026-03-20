<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    const TYPE_NORMAL = 'normal';
    const TYPE_MEDIO_MES = 'medio_mes';
    const TYPE_CON_RECARGO = 'con_recargo';

    protected $fillable = [
        'student_id',
        'payment_method_id',
        'amount',
        'expected_amount', 
        'payment_date',
        'payment_period',
        'payment_type',
        'notes'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'payment_period' => 'date',
        'amount' => 'decimal:2',
        'expected_amount' => 'decimal:2',
    ];

     /**
     * Devuelve el mapa de tipos de pago disponibles.
     */
    public static function paymentTypes(): array
    {
        return [
            self::TYPE_NORMAL      => 'Pago normal',
            self::TYPE_MEDIO_MES   => 'Pago medio mes',
            self::TYPE_CON_RECARGO => 'Pago con recargo',
        ];
    }

    /**
     * Etiqueta legible del tipo de pago.
     */
    public function getPaymentTypeLabelAttribute(): string
    {
        return static::paymentTypes()[$this->payment_type] ?? 'Pago normal';
    }
    
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}