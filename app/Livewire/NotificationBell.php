<?php

namespace App\Livewire;

use App\Models\AuditLog;
use Livewire\Component;

class NotificationBell extends Component
{
    public ?int $lastSeenId = null;
    public int $unreadCount = 0;
    public bool $isOpen = false;

    public function mount()
    {
        // Start from current max so we only announce new activities occurring from now
        $this->lastSeenId = AuditLog::max('id') ?? 0;
    }

    public function toggleOpen()
    {
        $this->isOpen = !$this->isOpen;
        if ($this->isOpen) {
            $this->unreadCount = 0;
        }
    }

    public function markAllAsRead()
    {
        $this->unreadCount = 0;
        $this->isOpen = false;
    }

    public function checkActivities()
    {
        if ($this->lastSeenId === null) {
            $this->lastSeenId = AuditLog::max('id') ?? 0;
            return;
        }

        $newLogs = AuditLog::with(['order.table', 'user'])
            ->where('id', '>', $this->lastSeenId)
            ->orderBy('id', 'asc')
            ->get();

        if ($newLogs->isNotEmpty()) {
            $notifications = $newLogs->map(function ($log) {
                $tableName = $log->order?->table?->table_number;
                $title = $this->formatTitle($log->action, $tableName);
                $speechText = $this->formatSpeech($log->action, $tableName, $log->remarks);

                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'title' => $title,
                    'message' => $log->remarks ?: 'New restaurant activity',
                    'table' => $tableName,
                    'time' => $log->created_at->format('h:i A'),
                    'speech' => $speechText,
                ];
            })->values()->toArray();

            $this->dispatch('play-activity-sound', notifications: $notifications);
            $this->unreadCount += $newLogs->count();
            $this->lastSeenId = $newLogs->max('id');
        }
    }

    protected function formatTitle(string $action, ?string $table): string
    {
        $prefix = $table ? "Table {$table}: " : "";

        return match ($action) {
            'order_created' => "{$prefix}New Order Placed",
            'order_cancelled' => "{$prefix}Order Cancelled",
            'item_added' => "{$prefix}New Item Added",
            'item_deleted' => "{$prefix}Item Removed",
            'bill_locked' => "{$prefix}Bill Locked",
            'bill_unlocked' => "{$prefix}Bill Unlocked",
            'bill_settled' => "{$prefix}Bill Settled & Paid",
            'table_shifted' => "{$prefix}Table Shifted",
            'table_merged' => "{$prefix}Table Merged",
            'kot_printed' => "{$prefix}KOT Sent to Kitchen",
            default => "{$prefix}Activity Update",
        };
    }

    protected function formatSpeech(string $action, ?string $table, ?string $remarks): string
    {
        $tablePart = $table ? "Table {$table}, " : "";

        return match ($action) {
            'order_created' => "New notification! {$tablePart}New order placed.",
            'order_cancelled' => "New notification! {$tablePart}Order has been cancelled.",
            'item_added' => "New notification! {$tablePart}Item added.",
            'bill_settled' => "New notification! {$tablePart}Bill settled successfully.",
            'bill_locked' => "New notification! {$tablePart}Bill locked for payment.",
            'table_shifted' => "New notification! {$tablePart}Table shifted.",
            default => "New notification! {$tablePart}" . ($remarks ?: 'Activity update'),
        };
    }

    public function render()
    {
        $recentLogs = AuditLog::with(['order.table', 'user'])
            ->latest('id')
            ->take(12)
            ->get();

        return view('livewire.notification-bell', [
            'recentLogs' => $recentLogs,
        ]);
    }
}
