<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\RestaurantTable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public function cancelOrder(int $orderId)
    {
        $order = Order::with('table')->findOrFail($orderId);
        if ($order->status === 'paid') {
            session()->flash('error', 'Paid order cannot be cancelled.');
            return;
        }

        $orderNumber = $order->order_number;
        $table = $order->table;
        $tableName = $table ? $table->table_number : 'Table';

        $order->update([
            'status' => 'cancelled',
            'notes' => trim(($order->notes ? $order->notes . ' | ' : '') . 'Cancelled by Admin (' . (auth()->user()->name ?? 'Admin') . ')'),
        ]);

        $order->items()->update(['kot_status' => 'cancelled']);

        if ($table) {
            $table->update([
                'status' => 'vacant',
                'current_order_id' => null,
            ]);
        }

        \App\Models\AuditLog::record(
            $order->id,
            'order_cancelled',
            ['status' => 'open'],
            ['status' => 'cancelled'],
            "Order {$orderNumber} (Table {$tableName}) was cancelled by Admin (" . (auth()->user()->name ?? 'Admin') . ")",
            auth()->id()
        );

        session()->flash('message', "Order {$orderNumber} cancelled successfully.");
    }

    public function render()
    {
        $today = Carbon::today();

        $todayRevenue = Order::whereDate('created_at', $today)
            ->where('status', 'paid')
            ->sum('final_payable_amount');

        $activeTablesCount = RestaurantTable::whereIn('status', ['occupied', 'locked'])->count();
        $totalTablesCount = RestaurantTable::count();

        $totalDiscountsToday = Order::whereDate('created_at', $today)
            ->sum(DB::raw('day_discount_amount + manual_discount_amount'));

        $paidOrdersCount = Order::whereDate('created_at', $today)
            ->where('status', 'paid')
            ->count();

        $avgOrderValue = $paidOrdersCount > 0 ? round($todayRevenue / $paidOrdersCount, 2) : 0;

        $recentOrders = Order::with('table')
            ->latest()
            ->take(8)
            ->get();

        $tables = RestaurantTable::orderBy('table_number')->get();

        return view('livewire.admin.dashboard', compact(
            'todayRevenue',
            'activeTablesCount',
            'totalTablesCount',
            'totalDiscountsToday',
            'paidOrdersCount',
            'avgOrderValue',
            'recentOrders',
            'tables'
        ))->layout('layouts.app', ['title' => 'Admin Overview - Dikshita Restro']);
    }
}
