<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rule_type',
        'day_of_week',
        'specific_date',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'specific_date' => 'date',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Find active discount rule for a specific date (defaults to today).
     * Priority: Specific Date Rule > Weekday Rule.
     */
    public static function getActiveRuleForDate(?Carbon $date = null): ?self
    {
        $date = $date ?: Carbon::now();
        $dateString = $date->toDateString();
        $dayOfWeek = strtolower($date->format('l')); // e.g. 'wednesday'

        // Check specific date rule first
        $specificRule = self::active()
            ->where('rule_type', 'specific_date')
            ->whereDate('specific_date', $dateString)
            ->first();

        if ($specificRule) {
            return $specificRule;
        }

        // Check weekday rule
        return self::active()
            ->where('rule_type', 'weekday')
            ->where(function ($q) use ($dayOfWeek) {
                $q->where('day_of_week', $dayOfWeek)
                  ->orWhere('day_of_week', strtolower($dayOfWeek));
            })
            ->first();
    }
}
