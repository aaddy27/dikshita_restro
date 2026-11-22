<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'image_path',
        'type',
        'base_price',
        'gst_percentage',
        'is_discount_excluded',
        'is_available',
    ];

    protected $appends = ['image_url', 'image'];

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }
        return null;
    }

    protected $casts = [
        'base_price' => 'decimal:2',
        'gst_percentage' => 'decimal:2',
        'is_discount_excluded' => 'boolean',
        'is_available' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ItemVariant::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isVeg(): bool
    {
        return $this->type === 'veg';
    }

    public function isNonVeg(): bool
    {
        return $this->type === 'non_veg';
    }

    public function hasVariants(): bool
    {
        return $this->variants->isNotEmpty();
    }

    public function getImageAttribute(): string
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }

        $name = strtolower($this->name);

        if (str_contains($name, 'world of veggies') || (str_contains($name, 'veggie') && str_contains($name, 'pizza'))) {
            return 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=500&auto=format&fit=crop&q=80';
        }
        if (str_contains($name, 'super hot spot') || str_contains($name, 'hot spot')) {
            return 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=500&auto=format&fit=crop&q=80';
        }
        if (str_contains($name, 'smoked peppers') || (str_contains($name, 'tikka') && str_contains($name, 'tandoori'))) {
            return 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=500&auto=format&fit=crop&q=80';
        }
        if (str_contains($name, 'paneer')) {
            return 'https://images.unsplash.com/photo-1567188040759-fb8a883dc6d8?w=500&auto=format&fit=crop&q=80';
        }
        if (str_contains($name, 'chicken') || str_contains($name, 'murgh')) {
            return 'https://images.unsplash.com/photo-1603894584373-5ac82b2ae398?w=500&auto=format&fit=crop&q=80';
        }
        if (str_contains($name, 'corn')) {
            return 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=500&auto=format&fit=crop&q=80';
        }
        if (str_contains($name, 'dal') || str_contains($name, 'makhani')) {
            return 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?w=500&auto=format&fit=crop&q=80';
        }
        if (str_contains($name, 'naan') || str_contains($name, 'bread') || str_contains($name, 'roti')) {
            return 'https://images.unsplash.com/photo-1601050690597-df0568f70950?w=500&auto=format&fit=crop&q=80';
        }
        if (str_contains($name, 'biryani')) {
            return 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?w=500&auto=format&fit=crop&q=80';
        }
        if (str_contains($name, 'brownie')) {
            return 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=500&auto=format&fit=crop&q=80';
        }
        if (str_contains($name, 'water')) {
            return 'https://images.unsplash.com/photo-1548839140-29a749e1bc4e?w=500&auto=format&fit=crop&q=80';
        }
        if (str_contains($name, 'mojito') || str_contains($name, 'drink') || str_contains($name, 'soda')) {
            return 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?w=500&auto=format&fit=crop&q=80';
        }

        return 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=500&auto=format&fit=crop&q=80';
    }
}
