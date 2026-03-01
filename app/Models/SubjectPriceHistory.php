<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectPriceHistory extends Model
{
    protected $table = 'subject_price_history';

    protected $fillable = [
        'subject_price_id',
        'subject_type_id',
        'has_teacher',
        'times_per_week',
        'old_price',
        'new_price',
        'changed_at',
    ];

    protected $casts = [
        'has_teacher' => 'boolean',
        'times_per_week' => 'integer',
        'old_price' => 'decimal:2',
        'new_price' => 'decimal:2',
        'changed_at' => 'datetime',
    ];

    /**
     * Relación con SubjectPrice
     */
    public function subjectPrice(): BelongsTo
    {
        return $this->belongsTo(SubjectPrice::class);
    }

    /**
     * Relación con SubjectType
     */
    public function subjectType(): BelongsTo
    {
        return $this->belongsTo(SubjectType::class);
    }

    /**
     * Registrar un cambio de precio en el historial
     *
     * @param SubjectPrice $subjectPrice
     * @param float|null $oldPrice
     * @param float $newPrice
     * @return self
     */
    public static function logPriceChange(SubjectPrice $subjectPrice, ?float $oldPrice, float $newPrice): self
    {
        return self::create([
            'subject_price_id' => $subjectPrice->id,
            'subject_type_id' => $subjectPrice->subject_type_id,
            'has_teacher' => $subjectPrice->has_teacher,
            'times_per_week' => $subjectPrice->times_per_week,
            'old_price' => $oldPrice,
            'new_price' => $newPrice,
            'changed_at' => now(),
        ]);
    }
}
