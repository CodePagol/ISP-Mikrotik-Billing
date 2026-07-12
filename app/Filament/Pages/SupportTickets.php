<?php

namespace App\Filament\Pages;

use App\Models\CustomersInfo;
use App\Models\NotificationLogs;
use App\Models\SupportTicket;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class SupportTickets extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected string $view = 'filament.pages.support-tickets';

    protected static ?string $navigationLabel = 'Support';

    protected static ?string $title = 'Support Tickets';

    protected static ?int $navigationSort = 6;

    public $customer;

    public $pppUser;

    public $tickets;

    // New ticket form
    public string $subject = '';

    public string $description = '';

    public string $priority = 'medium';

    public string $category = 'connection';

    public bool $showForm = false;

    // View/Edit ticket
    public ?int $viewingTicketId = null;

    public ?int $editingTicketId = null;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->guard('ppp')->check();
    }

    public function mount(): void
    {
        $user = Auth::guard('ppp')->user();
        if (! $user) {
            abort(403);
        }

        $this->pppUser = $user;
        $this->customer = CustomersInfo::where('ppp_user_id', $user->id)->first();
        $this->loadTickets();
    }

    public function loadTickets(): void
    {
        if (! $this->customer) {
            $this->tickets = collect();

            return;
        }

        $this->tickets = SupportTicket::where('customer_unique_id', $this->customer->customer_unique_id)
            ->orderByDesc('created_at')
            ->get();
    }

    public function toggleForm(): void
    {
        $this->showForm = ! $this->showForm;
        $this->viewingTicketId = null;
        $this->editingTicketId = null;
        if (! $this->showForm) {
            $this->resetForm();
        }
    }

    public function viewTicket(int $id): void
    {
        $this->viewingTicketId = $id;
        $this->showForm = false;
        $this->editingTicketId = null;
    }

    public function editTicket(int $id): void
    {
        $ticket = SupportTicket::findOrFail($id);

        // Prevent editing tickets of other customers
        if ($ticket->customer_unique_id !== $this->customer->customer_unique_id) {
            abort(403);
        }

        $this->editingTicketId = $id;
        $this->subject = $ticket->subject;
        $this->description = $ticket->description;
        $this->priority = $ticket->priority;
        $this->category = $ticket->category;
        $this->showForm = true;
        $this->viewingTicketId = null;
    }

    public function closeTicket(int $id): void
    {
        $ticket = SupportTicket::findOrFail($id);

        if ($ticket->customer_unique_id !== $this->customer->customer_unique_id) {
            abort(403);
        }

        $ticket->update(['status' => 'closed']);

        // Log notification of ticket closure
        NotificationLogs::create([
            'title' => 'Support Ticket Closed',
            'message' => "Customer {$this->customer->customer_name} ({$this->customer->customer_unique_id}) has closed support ticket #{$ticket->ticket_no}: {$ticket->subject}",
            'status' => 'Ticket Closed by User',
            'type' => 'Support Ticket',
        ]);

        $this->loadTickets();
        $this->dispatch('notify', type: 'success', message: 'Support ticket has been closed successfully.');
    }

    public function closeView(): void
    {
        $this->viewingTicketId = null;
    }

    public function submit(): void
    {
        $this->validate([
            'subject' => 'required|string|min:5|max:255',
            'description' => 'required|string|min:10|max:2000',
            'priority' => 'required|in:low,medium,high',
            'category' => 'required|in:billing,connection,speed,other',
        ]);

        if (! $this->customer) {
            return;
        }

        if ($this->editingTicketId) {
            $ticket = SupportTicket::findOrFail($this->editingTicketId);
            if ($ticket->customer_unique_id !== $this->customer->customer_unique_id) {
                abort(403);
            }

            $ticket->update([
                'subject' => $this->subject,
                'description' => $this->description,
                'priority' => $this->priority,
                'category' => $this->category,
            ]);

            // Alert on billing admin panel
            NotificationLogs::create([
                'title' => 'Support Ticket Updated',
                'message' => "Customer {$this->customer->customer_name} ({$this->customer->customer_unique_id}) has updated support ticket #{$ticket->ticket_no}: {$this->subject}",
                'status' => 'Ticket Updated by User',
                'type' => 'Support Ticket',
            ]);

            $this->dispatch('notify', type: 'success', message: 'Your support ticket has been updated successfully!');
        } else {
            $ticket = SupportTicket::create([
                'ticket_no' => SupportTicket::generateTicketNo(),
                'customer_unique_id' => $this->customer->customer_unique_id,
                'ppp_username' => $this->pppUser->username,
                'subject' => $this->subject,
                'description' => $this->description,
                'priority' => $this->priority,
                'category' => $this->category,
                'status' => 'open',
            ]);

            // Alert on billing admin panel
            NotificationLogs::create([
                'title' => 'New Support Ticket Created',
                'message' => "Customer {$this->customer->customer_name} ({$this->customer->customer_unique_id}) has opened a support ticket: #{$ticket->ticket_no} - {$this->subject}",
                'status' => 'New Ticket Created',
                'type' => 'Support Ticket',
            ]);

            $this->dispatch('notify', type: 'success', message: 'Your support ticket has been submitted successfully!');
        }

        $this->resetForm();
        $this->showForm = false;
        $this->editingTicketId = null;
        $this->loadTickets();
    }

    protected function resetForm(): void
    {
        $this->subject = '';
        $this->description = '';
        $this->priority = 'medium';
        $this->category = 'connection';
        $this->editingTicketId = null;
    }

    public function getViewingTicket(): ?SupportTicket
    {
        if (! $this->viewingTicketId) {
            return null;
        }

        return $this->tickets->firstWhere('id', $this->viewingTicketId);
    }

    public function getOpenCount(): int
    {
        return $this->tickets->whereIn('status', ['open', 'in_progress'])->count();
    }

    public function getResolvedCount(): int
    {
        return $this->tickets->whereIn('status', ['resolved', 'closed'])->count();
    }
}
