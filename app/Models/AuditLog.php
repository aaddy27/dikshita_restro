<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'action',
        'old_value',
        'new_value',
        'remarks',
        'ip_address',
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(
        ?int $orderId,
        string $action,
        mixed $oldValue = null,
        mixed $newValue = null,
        ?string $remarks = null,
        ?int $userId = null
    ): self {
        return self::create([
            'order_id' => $orderId,
            'user_id' => $userId ?: (Auth::id() ?? null),
            'action' => $action,
            'old_value' => is_array($oldValue) ? $oldValue : ($oldValue !== null ? ['value' => $oldValue] : null),
            'new_value' => is_array($newValue) ? $newValue : ($newValue !== null ? ['value' => $newValue] : null),
            'remarks' => $remarks,
            'ip_address' => Request::ip(),
        ]);
    }
}
