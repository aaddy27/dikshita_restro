<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'table_id',
        'status',
        'subtotal',
        'day_discount_amount',
        'manual_discount_amount',
        'tax_amount',
        'final_payable_amount',
        'payment_mode',
        'customer_name',
        'customer_phone',
        'notes',
        'locked_by',
        'locked_at',
        'paid_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'day_discount_amount' => 'decimal:2',
        'manual_discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'final_payable_amount' => 'decimal:2',
        'locked_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function lockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function getTotalDiscountAttribute(): float
    {
        return (float) ($this->day_discount_amount + $this->manual_discount_amount);
    }

    public function getCgstAmountAttribute(): float
    {
        return round($this->tax_amount / 2, 2);
    }

    public function getSgstAmountAttribute(): float
    {
        return round($this->tax_amount / 2, 2);
    }

    /**
     * Build dynamic UPI Payment Intent URL for exact final payable amount.
     */
    public function getUpiPaymentUrl(?float $amount = null): string
    {
        $upiId = config('app.restaurant_upi_id', 'dikshitarestro@upi');
        $merchantName = config('app.restaurant_upi_name', 'Dikshita Restro & Cafe');
        $targetAmount = number_format($amount !== null ? $amount : (float) $this->final_payable_amount, 2, '.', '');
        $note = 'Bill ' . $this->order_number;

        return 'upi://pay?pa=' . rawurlencode($upiId)
            . '&pn=' . rawurlencode($merchantName)
            . '&am=' . $targetAmount
            . '&tn=' . rawurlencode($note)
            . '&cu=INR';
    }

    /**
     * Generate inline SVG QR code for the UPI payment.
     */
    public function getUpiQrSvg(int $size = 180, ?float $amount = null): string
    {
        return \SimpleSoftwareIO\QrCode\Facades\QrCode::size($size)
            ->generate($this->getUpiPaymentUrl($amount));
    }

    /**
     * Generate clean inline SVG QR code without XML prolog.
     */
    public function getUpiQrCleanSvg(int $size = 180, ?float $amount = null): string
    {
        $svg = (string) $this->getUpiQrSvg($size, $amount);
        return preg_replace('/<\?xml[^\?]*\?>\s*/i', '', $svg);
    }

    /**
     * Generate base64 data URI SVG QR code for reliable thermal print & PDF rendering.
     */
    public function getUpiQrBase64(int $size = 180, ?float $amount = null): string
    {
        $svg = (string) $this->getUpiQrSvg($size, $amount);
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
