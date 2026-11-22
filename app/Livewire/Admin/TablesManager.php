<?php

namespace App\Livewire\Admin;

use App\Models\RestaurantTable;
use Illuminate\Support\Str;
use Livewire\Component;

class TablesManager extends Component
{
    public bool $showCreateModal = false;
    public string $table_number = '';
    public int $capacity = 4;

    public function createTable()
    {
        $this->validate([
            'table_number' => 'required|string|unique:restaurant_tables,table_number',
            'capacity' => 'required|integer|min:1',
        ]);

        RestaurantTable::create([
            'table_number' => strtoupper(trim($this->table_number)),
            'capacity' => $this->capacity,
            'qr_token' => (string) Str::uuid(),
            'status' => 'vacant',
        ]);

        $this->table_number = '';
        $this->capacity = 4;
        $this->showCreateModal = false;
        session()->flash('message', 'Table created successfully with unique QR token.');
    }

    public function regenerateQr(int $tableId)
    {
        $table = RestaurantTable::findOrFail($tableId);
        $table->update(['qr_token' => (string) Str::uuid()]);
        session()->flash('message', "QR Token regenerated for Table {$table->table_number}.");
    }

    public function deleteTable(int $tableId)
    {
        $table = RestaurantTable::findOrFail($tableId);
        if ($table->status !== 'vacant') {
            session()->flash('error', "Cannot delete Table {$table->table_number} because it is currently {$table->status}.");
            return;
        }

        $table->delete();
        session()->flash('message', "Table {$table->table_number} deleted successfully.");
    }

    public function render()
    {
        $tables = RestaurantTable::with('currentOrder')->orderBy('table_number')->get();

        return view('livewire.admin.tables-manager', compact('tables'))
            ->layout('layouts.app', ['title' => 'Tables & QR Standees - Dikshita Restro']);
    }
}
