<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class RestaurantTable extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_number',
        'capacity',
        'qr_token',
        'status',
        'current_order_id',
    ];

    protected $casts = [
        'capacity' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($table) {
            if (empty($table->qr_token)) {
                $table->qr_token = (string) Str::uuid();
            }
        });
    }

    public function currentOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'current_order_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'table_id');
    }

    public function isVacant(): bool
    {
        return $this->status === 'vacant';
    }

    public function isOccupied(): bool
    {
        return $this->status === 'occupied';
    }

    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }

    public function getQrUrlAttribute(): string
    {
        return url('/table/' . $this->qr_token);
    }
}
