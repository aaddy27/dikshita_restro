<?php

namespace App\Livewire\Customer;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use App\Services\OrderCalculationService;
use Illuminate\Support\Str;
use Livewire\Component;

class QrOrder extends Component
{
    public string $qr_token;
    public ?int $table_id = null;
    public ?string $table_number = null;
    public string $table_status = 'vacant';
    public ?int $active_order_id = null;
    public ?string $order_status = null;

    // Filters
    public string $search = '';
    public ?int $selectedCategory = null;
    public string $typeFilter = 'all'; // all, veg, non_veg

    // Cart: array of items: [key => ['menu_item_id', 'item_variant_id', 'name', 'variant_name', 'price', 'qty', 'is_discount_excluded']]
    public array $cart = [];

    // Variant Modal
    public bool $showVariantModal = false;
    public ?int $modalItemId = null;
    public ?MenuItem $modalItem = null;

    // Customer info for checkout
    public string $customer_name = '';
    public string $customer_phone = '';
    public string $notes = '';

    public bool $showCartDrawer = false;
    public bool $showBillModal = false;
    public bool $orderPlacedSuccess = false;

    public function openBillModal()
    {
        $this->showBillModal = true;
    }

    public function closeBillModal()
    {
        $this->showBillModal = false;
    }

    public function mount(string $qr_token)
    {
        $this->qr_token = $qr_token;
        $this->loadTableState();
    }

    public function loadTableState()
    {
        $table = RestaurantTable::where('qr_token', $this->qr_token)->firstOrFail();
        $this->table_id = $table->id;
        $this->table_number = $table->table_number;
        $this->table_status = $table->status;

        $order = Order::where('table_id', $table->id)
            ->whereIn('status', ['open', 'locked'])
            ->latest()
            ->first();

        if ($order) {
            $this->active_order_id = $order->id;
            $this->order_status = $order->status;
            if (!$this->customer_name) {
                $this->customer_name = $order->customer_name ?? '';
            }
            if (!$this->customer_phone) {
                $this->customer_phone = $order->customer_phone ?? '';
            }
        } else {
            $this->active_order_id = null;
            $this->order_status = null;
        }
    }

    public function openItem(int $itemId)
    {
        if ($this->order_status === 'locked') {
            return;
        }

        $item = MenuItem::with('variants')->findOrFail($itemId);

        if ($item->variants->isNotEmpty()) {
            $this->modalItemId = $itemId;
            $this->modalItem = $item;
            $this->showVariantModal = true;
        } else {
            $this->addToCartDirect($item);
        }
    }

    public function addToCartDirect(MenuItem $item)
    {
        if ($this->order_status === 'locked') {
            return;
        }

        $key = 'item_' . $item->id;
        if (isset($this->cart[$key])) {
            $this->cart[$key]['qty']++;
        } else {
            $this->cart[$key] = [
                'menu_item_id' => $item->id,
                'item_variant_id' => null,
                'name' => $item->name,
                'variant_name' => null,
                'price' => (float) $item->base_price,
                'qty' => 1,
                'is_discount_excluded' => (bool) $item->is_discount_excluded,
                'type' => $item->type,
            ];
        }
    }

    public function addVariantToCart(int $variantId)
    {
        if ($this->order_status === 'locked') {
            return;
        }

        $item = MenuItem::with('variants')->findOrFail($this->modalItemId);
        $variant = $item->variants->firstWhere('id', $variantId);

        if (!$variant) {
            return;
        }

        $key = 'variant_' . $variant->id;
        if (isset($this->cart[$key])) {
            $this->cart[$key]['qty']++;
        } else {
            $this->cart[$key] = [
                'menu_item_id' => $item->id,
                'item_variant_id' => $variant->id,
                'name' => $item->name,
                'variant_name' => $variant->name,
                'price' => (float) $variant->price,
                'qty' => 1,
                'is_discount_excluded' => (bool) $item->is_discount_excluded,
                'type' => $item->type,
            ];
        }

        $this->showVariantModal = false;
        $this->modalItem = null;
    }

    public function updateQty(string $key, int $delta)
    {
        if ($this->order_status === 'locked') {
            return;
        }

        if (!isset($this->cart[$key])) {
            return;
        }

        $this->cart[$key]['qty'] += $delta;
        if ($this->cart[$key]['qty'] <= 0) {
            unset($this->cart[$key]);
        }
    }

    public function getCartTotalProperty(): float
    {
        $sum = 0.0;
        foreach ($this->cart as $item) {
            $sum += $item['qty'] * $item['price'];
        }
        return round($sum, 2);
    }

    public function getCartCountProperty(): int
    {
        $count = 0;
        foreach ($this->cart as $item) {
            $count += $item['qty'];
        }
        return $count;
    }

    public function submitOrder(OrderCalculationService $calcService)
    {
        $this->loadTableState();

        if ($this->order_status === 'locked') {
            $this->addError('lock', 'This table bill is locked. Contact cashier to add more items.');
            return;
        }

        if (empty($this->cart)) {
            $this->addError('cart', 'Cart is empty. Please select items first.');
            return;
        }

        $table = RestaurantTable::findOrFail($this->table_id);

        // Find or create open order
        $order = Order::where('table_id', $table->id)
            ->where('status', 'open')
            ->first();

        if (!$order) {
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(6)),
                'table_id' => $table->id,
                'status' => 'open',
                'customer_name' => $this->customer_name ?: 'Table Guest',
                'customer_phone' => $this->customer_phone ?: null,
                'notes' => $this->notes ?: null,
            ]);

            $table->update([
                'status' => 'occupied',
                'current_order_id' => $order->id,
            ]);
        } else {
            // Update customer details if provided
            if ($this->customer_name && empty($order->customer_name)) {
                $order->customer_name = $this->customer_name;
            }
            if ($this->customer_phone && empty($order->customer_phone)) {
                $order->customer_phone = $this->customer_phone;
            }
            if ($this->notes) {
                $order->notes = ($order->notes ? $order->notes . ' | ' : '') . $this->notes;
            }
            $order->save();
        }

        // Add items to order
        foreach ($this->cart as $cartItem) {
            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $cartItem['menu_item_id'],
                'item_variant_id' => $cartItem['item_variant_id'],
                'item_name' => $cartItem['name'],
                'variant_name' => $cartItem['variant_name'],
                'qty' => $cartItem['qty'],
                'unit_price' => $cartItem['price'],
                'total_price' => round($cartItem['qty'] * $cartItem['price'], 2),
                'kot_status' => 'pending', // Pending KOT
                'added_by_type' => 'customer',
            ]);
        }

        // Recalculate totals
        $calcService->syncOrderTotals($order);

        // Reset cart
        $this->cart = [];
        $this->notes = '';
        $this->showCartDrawer = false;
        $this->orderPlacedSuccess = true;
        $this->loadTableState();
    }

    public function render()
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        $query = MenuItem::with('variants')
            ->where('is_available', true);

        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }

        if ($this->typeFilter !== 'all') {
            $query->where('type', $this->typeFilter);
        }

        if (trim($this->search) !== '') {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        $menuItems = $query->get();

        $activeOrder = null;
        if ($this->active_order_id) {
            $activeOrder = Order::with('items')->find($this->active_order_id);
        }

        return view('livewire.customer.qr-order', compact('categories', 'menuItems', 'activeOrder'))
            ->layout('layouts.customer', ['title' => 'Table ' . $this->table_number . ' - Dikshita Restro']);
    }
}
