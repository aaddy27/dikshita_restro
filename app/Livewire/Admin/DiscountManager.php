<?php

namespace App\Livewire\Admin;

use App\Models\DiscountRule;
use Livewire\Component;

class DiscountManager extends Component
{
    public bool $showModal = false;
    public ?int $editingRuleId = null;

    public string $name = '';
    public string $rule_type = 'weekday'; // weekday, specific_date
    public string $day_of_week = 'wednesday';
    public string $specific_date = '';
    public string $discount_type = 'percentage'; // percentage, flat
    public float $discount_value = 10.0;
    public float $min_order_amount = 0.0;
    public bool $is_active = true;

    public function openCreate()
    {
        $this->reset(['editingRuleId', 'name', 'specific_date']);
        $this->rule_type = 'weekday';
        $this->day_of_week = 'wednesday';
        $this->discount_type = 'percentage';
        $this->discount_value = 15.0;
        $this->min_order_amount = 200.0;
        $this->is_active = true;
        $this->showModal = true;
    }

    public function saveRule()
    {
        $this->validate([
            'name' => 'required|string',
            'rule_type' => 'required|in:weekday,specific_date',
            'discount_type' => 'required|in:percentage,flat',
            'discount_value' => 'required|numeric|min:0.1',
        ]);

        DiscountRule::updateOrCreate(
            ['id' => $this->editingRuleId],
            [
                'name' => $this->name,
                'rule_type' => $this->rule_type,
                'day_of_week' => $this->rule_type === 'weekday' ? strtolower($this->day_of_week) : null,
                'specific_date' => $this->rule_type === 'specific_date' ? $this->specific_date : null,
                'discount_type' => $this->discount_type,
                'discount_value' => $this->discount_value,
                'min_order_amount' => $this->min_order_amount,
                'is_active' => $this->is_active,
            ]
        );

        $this->showModal = false;
        session()->flash('message', 'Discount rule saved successfully.');
    }

    public function toggleRule(int $id)
    {
        $rule = DiscountRule::findOrFail($id);
        $rule->is_active = !$rule->is_active;
        $rule->save();
    }

    public function deleteRule(int $id)
    {
        DiscountRule::findOrFail($id)->delete();
        session()->flash('message', 'Discount rule removed.');
    }

    public function render()
    {
        $rules = DiscountRule::orderBy('created_at', 'desc')->get();

        return view('livewire.admin.discount-manager', compact('rules'))
            ->layout('layouts.app', ['title' => 'Day-Wise Discount Engine - Dikshita Restro']);
    }
}
