<?php

namespace App\Livewire;

use App\Models\NotificationLogs;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationList extends Component
{
    public $notificationCount = 0;

    public $notifications = [];

    public function mount()
    {
        static $count = null;
        if ($count === null) {
            $count = NotificationLogs::whereNull('read_by')->count();
        }
        $this->notificationCount = $count;
    }

    public function loadNotifications()
    {
        $this->notifications = NotificationLogs::latest()->take(5)->get();

        $this->notificationCount = NotificationLogs::whereNull('read_by')->count();
    }

    public function markAsRead($id)
    {
        NotificationLogs::where('id', $id)->update([
            'read_by' => Auth::user()->name,
            'read_at' => Carbon::now(),
        ]);

        // Notification marked as read and updated in the database
        $this->loadNotifications();

        // Optionally, you can use a flash message to notify the user
        flash()->success('Notification marked as read');
    }

    public function markAllAsRead()
    {
        NotificationLogs::whereNull('read_by')->update([
            'read_by' => Auth::user()->name,
            'read_at' => Carbon::now(),
        ]);

        // All notifications marked as read and updated in the database
        $this->loadNotifications();

        // Optionally, you can use a flash message to notify the user
        flash()->success('All notifications marked as read');
    }

    public function render()
    {
        return view('livewire.notification-list'); // default view without layout
    }
}
