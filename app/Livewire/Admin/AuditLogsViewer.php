<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use Livewire\Component;

class AuditLogsViewer extends Component
{
    public string $actionFilter = 'all';
    public string $search = '';

    public function render()
    {
        $query = AuditLog::with(['order.table', 'user'])->latest();

        if ($this->actionFilter !== 'all') {
            $query->where('action', $this->actionFilter);
        }

        if (trim($this->search) !== '') {
            $query->where(function ($q) {
                $q->where('remarks', 'like', '%' . $this->search . '%')
                  ->orWhereHas('order', function ($oq) {
                      $oq->where('order_number', 'like', '%' . $this->search . '%');
                  });
            });
        }

        $logs = $query->paginate(25);

        return view('livewire.admin.audit-logs-viewer', compact('logs'))
            ->layout('layouts.app', ['title' => 'Security Audit Logs - Dikshita Restro']);
    }
}
