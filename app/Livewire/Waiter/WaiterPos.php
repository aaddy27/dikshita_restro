<?php

namespace App\Livewire\Waiter;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use App\Services\OrderCalculationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class WaiterPos extends Component
{
    public ?int $selectedTableId = null;
    public string $searchItem = '';
    public ?int $selectedCategory = null;
    public string $typeFilter = 'all';

    public function selectTable(int $tableId)
    {
        $this->selectedTableId = $tableId;
        $this->searchItem = '';
    }

    public function closeTable()
    {
        $this->selectedTableId = null;
    }

    public function startOrder(int $tableId)
    {
        $table = RestaurantTable::findOrFail($tableId);
        if ($table->status === 'locked') {
            return;
        }

        $order = Order::create([
            'order_number' => 'ORD-' . strtoupper(Str::random(6)),
            'table_id' => $table->id,
            'status' => 'open',
            'customer_name' => 'Table ' . $table->table_number,
        ]);

        $table->update([
            'status' => 'occupied',
            'current_order_id' => $order->id,
        ]);

        $this->selectedTableId = $table->id;
    }

    public function addItem(int $menuItemId, ?int $variantId = null, OrderCalculationService $calcService)
    {
        $table = RestaurantTable::findOrFail($this->selectedTableId);
        $order = Order::where('table_id', $table->id)->where('status', 'open')->first();

        if (!$order) {
            $this->startOrder($table->id);
            $order = Order::where('table_id', $table->id)->where('status', 'open')->first();
        }

        if ($order->isLocked()) {
            return;
        }

        $menuItem = MenuItem::with('variants')->findOrFail($menuItemId);
        $variant = $variantId ? $menuItem->variants->firstWhere('id', $variantId) : null;

        $price = $variant ? (float) $variant->price : (float) $menuItem->base_price;
        $variantName = $variant ? $variant->name : null;

        // Check if matching pending item exists
        $existing = OrderItem::where('order_id', $order->id)
            ->where('menu_item_id', $menuItem->id)
            ->where('item_variant_id', $variantId)
            ->where('kot_status', 'pending')
            ->first();

        if ($existing) {
            $existing->qty += 1;
            $existing->total_price = round($existing->qty * $existing->unit_price, 2);
            $existing->save();
        } else {
            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $menuItem->id,
                'item_variant_id' => $variantId,
                'item_name' => $menuItem->name,
                'variant_name' => $variantName,
                'qty' => 1,
                'unit_price' => $price,
                'total_price' => $price,
                'kot_status' => 'pending',
                'added_by_type' => 'waiter',
                'added_by_id' => Auth::id(),
            ]);
        }

        $calcService->syncOrderTotals($order);
    }

    public function updateItemQty(int $orderItemId, int $delta, OrderCalculationService $calcService)
    {
        $item = OrderItem::with('order')->findOrFail($orderItemId);
        if ($item->order->isLocked()) {
            return;
        }

        $newQty = $item->qty + $delta;
        if ($newQty <= 0) {
            AuditLog::record(
                $item->order_id,
                'item_deleted',
                ['item' => $item->item_name, 'qty' => $item->qty],
                null,
                'Waiter deleted ' . $item->item_name . ' from order.',
                Auth::id()
            );
            $item->delete();
        } else {
            $item->qty = $newQty;
            $item->total_price = round($newQty * $item->unit_price, 2);
            $item->save();
        }

        $calcService->syncOrderTotals($item->order);
    }

    public function triggerKot()
    {
        $table = RestaurantTable::findOrFail($this->selectedTableId);
        $order = Order::with('items')->where('table_id', $table->id)->where('status', 'open')->first();

        if (!$order) {
            return;
        }

        $pendingItems = $order->items->where('kot_status', 'pending');
        if ($pendingItems->isEmpty()) {
            return;
        }

        // Mark as printed
        foreach ($pendingItems as $item) {
            $item->update(['kot_status' => 'printed']);
        }

        AuditLog::record(
            $order->id,
            'kot_printed',
            null,
            ['count' => $pendingItems->count()],
            'Waiter triggered KOT for ' . $pendingItems->count() . ' items.',
            Auth::id()
        );

        $this->dispatch('open-kot-window', url: route('order.kot', ['order' => $order->id, 'all' => 0, 'autoprint' => 1]));
    }

    public function render()
    {
        $tables = RestaurantTable::with(['currentOrder.items'])->orderBy('table_number')->get();
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        $selectedTable = null;
        $selectedOrder = null;
        $menuItems = collect();

        if ($this->selectedTableId) {
            $selectedTable = RestaurantTable::with('currentOrder.items')->find($this->selectedTableId);
            if ($selectedTable) {
                $selectedOrder = Order::with(['items.menuItem', 'items.variant'])
                    ->where('table_id', $selectedTable->id)
                    ->whereIn('status', ['open', 'locked'])
                    ->latest()
                    ->first();

                $query = MenuItem::with('variants')->where('is_available', true);

                if ($this->selectedCategory) {
                    $query->where('category_id', $this->selectedCategory);
                }

                if ($this->typeFilter !== 'all') {
                    $query->where('type', $this->typeFilter);
                }

                if (trim($this->searchItem) !== '') {
                    $query->where('name', 'like', '%' . $this->searchItem . '%');
                }

                $menuItems = $query->take(24)->get();
            }
        }

        return view('livewire.waiter.waiter-pos', compact('tables', 'categories', 'selectedTable', 'selectedOrder', 'menuItems'))
            ->layout('layouts.app', ['title' => 'Waiter Tables & KOT - Dikshita Restro']);
    }
}
