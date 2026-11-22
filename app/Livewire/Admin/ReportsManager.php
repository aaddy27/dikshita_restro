<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ReportsManager extends Component
{
    public string $selectedDate = '';

    public function mount()
    {
        $this->selectedDate = Carbon::today()->toDateString();
    }

    public function render()
    {
        $date = Carbon::parse($this->selectedDate ?: today());

        $orders = Order::with('table')
            ->whereDate('created_at', $date)
            ->where('status', 'paid')
            ->orderBy('paid_at', 'desc')
            ->get();

        $totalRevenue = $orders->sum('final_payable_amount');
        $totalSubtotal = $orders->sum('subtotal');
        $totalTaxes = $orders->sum('tax_amount');
        $totalDiscounts = $orders->sum('total_discount');

        $cashTotal = $orders->where('payment_mode', 'cash')->sum('final_payable_amount');
        $upiTotal = $orders->where('payment_mode', 'upi')->sum('final_payable_amount');
        $cardTotal = $orders->where('payment_mode', 'card')->sum('final_payable_amount');

        return view('livewire.admin.reports-manager', compact(
            'orders',
            'totalRevenue',
            'totalSubtotal',
            'totalTaxes',
            'totalDiscounts',
            'cashTotal',
            'upiTotal',
            'cardTotal',
            'date'
        ))->layout('layouts.app', ['title' => 'Daily Sales Reports - Dikshita Restro']);
    }
}
