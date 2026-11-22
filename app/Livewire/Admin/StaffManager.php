<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class StaffManager extends Component
{
    public bool $showModal = false;
    public ?int $editingUserId = null;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = 'waiter'; // admin, cashier, waiter
    public string $security_pin = '';
    public string $phone = '';

    public function openCreate()
    {
        $this->reset(['editingUserId', 'name', 'email', 'password', 'phone']);
        $this->role = 'waiter';
        $this->security_pin = '1234';
        $this->showModal = true;
    }

    public function editUser(int $id)
    {
        $user = User::findOrFail($id);
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->role = $user->role;
        $this->security_pin = $user->security_pin ?? '';
        $this->phone = $user->phone ?? '';
        $this->showModal = true;
    }

    public function saveUser()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . ($this->editingUserId ?: 'NULL'),
            'role' => 'required|in:admin,cashier,waiter',
            'security_pin' => 'nullable|string|max:10',
        ];

        if (!$this->editingUserId) {
            $rules['password'] = 'required|min:6';
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'security_pin' => $this->role === 'cashier' ? ($this->security_pin ?: '1234') : ($this->security_pin ?: null),
            'phone' => $this->phone,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        User::updateOrCreate(['id' => $this->editingUserId], $data);

        $this->showModal = false;
        session()->flash('message', 'Staff user saved successfully.');
    }

    public function deleteUser(int $id)
    {
        if ($id === auth()->id()) {
            session()->flash('error', 'Cannot delete your own active account.');
            return;
        }

        User::findOrFail($id)->delete();
        session()->flash('message', 'Staff member removed.');
    }

    public function render()
    {
        $users = User::orderBy('name')->get();

        return view('livewire.admin.staff-manager', compact('users'))
            ->layout('layouts.app', ['title' => 'Staff & PIN Security - Dikshita Restro']);
    }
}
