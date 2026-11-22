<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\RestaurantTable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
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
