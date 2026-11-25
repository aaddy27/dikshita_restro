<?php

namespace App\Livewire\Kitchen;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use Carbon\Carbon;
use Livewire\Component;

class KitchenDisplay extends Component
{
    public string $filter = 'active'; // 'active', 'all'
    public ?string $tableFilter = null;
    public string $lastRefreshedAt = '';

    public function mount()
    {
        $this->lastRefreshedAt = now()->format('h:i:s A');
    }

    public function setFilter(string $filter)
    {
        $this->filter = $filter;
    }

    public function markItemCooking(int $orderItemId)
    {
        $item = OrderItem::find($orderItemId);
        if ($item) {
            $item->update(['kot_status' => 'cooking']);
            $this->dispatch('item-updated');
        }
    }

    public function markItemPrepared(int $orderItemId)
    {
        $item = OrderItem::find($orderItemId);
        if ($item) {
            $item->update(['kot_status' => 'prepared']);
            $this->dispatch('item-updated');
        }
    }

    public function markOrderAllPrepared(int $orderId)
    {
        $order = Order::find($orderId);
        if ($order) {
            $order->items()->whereIn('kot_status', ['pending', 'printed', 'cooking'])->update([
                'kot_status' => 'prepared',
            ]);
            $this->dispatch('order-prepared');
        }
    }

    public function render()
    {
        $this->lastRefreshedAt = now()->format('h:i:s A');

        // Fetch active running orders (open or locked) that have items
        $ordersQuery = Order::with(['table', 'items.menuItem', 'items.variant'])
            ->whereIn('status', ['open', 'locked'])
            ->has('items')
            ->orderBy('created_at', 'asc');

        if ($this->tableFilter) {
            $ordersQuery->whereHas('table', function ($q) {
                $q->where('table_number', $this->tableFilter);
            });
        }

        $allOrders = $ordersQuery->get();

        // Calculate Kitchen KPI stats (100% Pure Veg)
        $totalItemsToCook = 0;
        $vegItemsToCook = 0;

        foreach ($allOrders as $ord) {
            foreach ($ord->items as $it) {
                if (in_array($it->kot_status, ['pending', 'printed', 'cooking'])) {
                    $totalItemsToCook += $it->qty;
                    $vegItemsToCook += $it->qty;
                }
            }
        }

        // Filter orders based on active filter
        $filteredOrders = $allOrders->filter(function ($order) {
            if ($this->filter === 'active') {
                return $order->items->contains(function ($item) {
                    return in_array($item->kot_status, ['pending', 'printed', 'cooking']);
                });
            }

            return true;
        });

        $tables = RestaurantTable::orderBy('table_number')->get();

        return view('livewire.kitchen.kitchen-display', [
            'orders' => $filteredOrders,
            'tables' => $tables,
            'totalItemsToCook' => $totalItemsToCook,
            'vegItemsToCook' => $vegItemsToCook,
        ])->layout('layouts.app', ['title' => "Kitchen Display (Cook Portal) - Deekshita's Restaurant"]);
    }
}
