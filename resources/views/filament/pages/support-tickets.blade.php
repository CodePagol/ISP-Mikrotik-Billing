<x-filament-panels::page>
    <div class="cp-space-y-6">

        {{-- Header Banner --}}
        <div class="cp-relative cp-overflow-hidden cp-rounded-3xl cp-bg-gradient-to-br cp-from-indigo-600 cp-to-purple-600 cp-p-6 cp-text-white cp-shadow-2xl cp-shadow-indigo-900/30">
            <div class="cp-absolute cp-right-0 cp-top-0 cp-opacity-10 cp-pointer-events-none">
                <svg viewBox="0 0 200 200" width="200" height="200"><circle cx="170" cy="30" r="100" fill="white"/></svg>
            </div>
            <div class="cp-relative cp-flex cp-flex-col md:cp-flex-row cp-justify-between cp-items-start md:cp-items-center cp-gap-4">
                <div>
                    <p class="cp-text-indigo-200 cp-text-sm cp-font-medium cp-mb-1">Account: {{ $customer?->customer_unique_id ?? 'N/A' }}</p>
                    <h2 class="cp-text-2xl cp-font-black">Support Center</h2>
                    <p class="cp-text-indigo-100 cp-text-sm cp-mt-1">Submit tickets and get assistance from our support team</p>
                </div>
                @if(!$showForm && !$viewingTicketId && $customer)
                    <button wire:click="toggleForm"
                        class="cp-flex cp-items-center cp-gap-2 cp-px-5 cp-py-3 cp-bg-white cp-text-indigo-600 hover:cp-bg-indigo-50 cp-font-bold cp-rounded-2xl cp-shadow-lg cp-transition-all cp-duration-200 hover:cp--translate-y-0.5 active:cp-translate-y-0">
                        <svg class="cp-w-5 cp-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        Create New Ticket
                    </button>
                @endif
            </div>
        </div>

        @if(!$customer)
            <div class="cp-p-6 cp-bg-amber-500/10 cp-border cp-border-amber-500/20 cp-text-amber-600 dark:cp-text-amber-400 cp-rounded-3xl cp-text-center">
                <svg style="width: 48px; height: 48px; min-width: 48px; min-height: 48px;" class="cp-text-amber-500 cp-mx-auto cp-mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <h3 class="cp-text-lg cp-font-bold cp-mb-1">Customer Profile Not Found</h3>
                <p class="cp-text-sm">Your PPPoE account is active, but it is not linked to a customer profile. Support tickets require a registered profile. Please contact support to complete your profile.</p>
            </div>
        @else
            @if(!$showForm && !$viewingTicketId)
            {{-- Stats Cards --}}
            <div class="cp-grid cp-grid-cols-1 md:cp-grid-cols-3 cp-gap-4">
                
                <!-- Total Tickets -->
                <div class="cp-bg-white dark:cp-bg-slate-900/60 cp-border cp-border-gray-100 dark:cp-border-white/5 cp-shadow-lg cp-rounded-3xl cp-p-5 cp-flex cp-items-center cp-justify-between">
                    <div>
                        <span class="cp-text-gray-400 dark:cp-text-slate-400 cp-text-xs cp-font-semibold cp-uppercase cp-tracking-wider cp-block">Total Tickets</span>
                        <span class="cp-text-2xl cp-font-black cp-mt-1.5 cp-block cp-text-gray-900 dark:cp-text-white">{{ $tickets->count() }}</span>
                    </div>
                    <div class="cp-p-3 cp-bg-indigo-500/10 cp-text-indigo-500 cp-rounded-2xl">
                        <svg style="width: 28px; height: 28px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Open Tickets -->
                <div class="cp-bg-white dark:cp-bg-slate-900/60 cp-border cp-border-gray-100 dark:cp-border-white/5 cp-shadow-lg cp-rounded-3xl cp-p-5 cp-flex cp-items-center cp-justify-between">
                    <div>
                        <span class="cp-text-gray-400 dark:cp-text-slate-400 cp-text-xs cp-font-semibold cp-uppercase cp-tracking-wider cp-block">Open & Pending</span>
                        <span class="cp-text-2xl cp-font-black cp-mt-1.5 cp-block cp-text-amber-500">{{ $this->getOpenCount() }}</span>
                    </div>
                    <div class="cp-p-3 cp-bg-amber-500/10 cp-text-amber-500 cp-rounded-2xl">
                        <svg style="width: 28px; height: 28px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Resolved Tickets -->
                <div class="cp-bg-white dark:cp-bg-slate-900/60 cp-border cp-border-gray-100 dark:cp-border-white/5 cp-shadow-lg cp-rounded-3xl cp-p-5 cp-flex cp-items-center cp-justify-between">
                    <div>
                        <span class="cp-text-gray-400 dark:cp-text-slate-400 cp-text-xs cp-font-semibold cp-uppercase cp-tracking-wider cp-block">Resolved & Closed</span>
                        <span class="cp-text-2xl cp-font-black cp-mt-1.5 cp-block cp-text-emerald-500">{{ $this->getResolvedCount() }}</span>
                    </div>
                    <div class="cp-p-3 cp-bg-emerald-500/10 cp-text-emerald-500 cp-rounded-2xl">
                        <svg style="width: 28px; height: 28px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>

            </div>

            {{-- Tickets List --}}
            <div class="cp-bg-white dark:cp-bg-slate-900/60 cp-border cp-border-gray-100 dark:cp-border-white/5 cp-rounded-3xl cp-shadow-xl cp-overflow-hidden">
                <div class="cp-p-6 cp-border-b cp-border-gray-100 dark:cp-border-white/5">
                    <h3 class="cp-text-lg cp-font-bold cp-text-gray-900 dark:cp-text-white">Your Support Tickets</h3>
                </div>

                @if($tickets->isEmpty())
                    <div class="cp-p-12 cp-text-center cp-flex cp-flex-col cp-items-center cp-justify-center">
                        <div class="cp-w-16 cp-cp-h-16 cp-mb-4 cp-p-4 cp-bg-gray-50 dark:cp-bg-slate-800 cp-rounded-full cp-text-gray-400 dark:cp-text-slate-500">
                            <svg class="cp-w-8 cp-h-8 cp-mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <h4 class="cp-text-base cp-font-bold cp-text-gray-900 dark:cp-text-white cp-mb-1">No Tickets Found</h4>
                        <p class="cp-text-sm cp-text-gray-400 dark:cp-text-slate-400 cp-max-w-md cp-mb-6">You haven't submitted any support tickets yet. If you are experiencing issues, please create a ticket.</p>
                        <button wire:click="toggleForm" class="cp-px-5 cp-py-2.5 cp-bg-indigo-600 hover:cp-bg-indigo-500 cp-text-white cp-font-bold cp-rounded-xl cp-transition-colors">
                            Submit Your First Ticket
                        </button>
                    </div>
                @else
                    <div class="cp-divide-y cp-divide-gray-100 dark:cp-divide-white/5">
                        @foreach($tickets as $ticket)
                            <div class="cp-p-6 cp-flex cp-flex-col md:cp-flex-row cp-justify-between cp-items-start md:cp-items-center cp-gap-4 hover:cp-bg-gray-50/50 dark:hover:cp-bg-white/5 cp-transition-colors">
                                <div class="cp-space-y-1.5">
                                    <div class="cp-flex cp-items-center cp-gap-2.5 cp-flex-wrap">
                                        <span class="cp-text-xs cp-font-bold cp-text-indigo-500 dark:cp-text-indigo-400">{{ $ticket->ticket_no }}</span>
                                        <span class="cp-inline-flex cp-items-center cp-px-2 cp-py-0.5 cp-rounded-md cp-text-[10px] cp-font-bold cp-uppercase cp-tracking-wider
                                            {{ $ticket->status === 'open' ? 'cp-bg-amber-500/10 cp-text-amber-500' : '' }}
                                            {{ $ticket->status === 'in_progress' ? 'cp-bg-blue-500/10 cp-text-blue-500' : '' }}
                                            {{ $ticket->status === 'resolved' ? 'cp-bg-emerald-500/10 cp-text-emerald-500' : '' }}
                                            {{ $ticket->status === 'closed' ? 'cp-bg-gray-500/10 cp-text-gray-500' : '' }}">
                                            {{ str_replace('_', ' ', $ticket->status) }}
                                        </span>
                                        <span class="cp-inline-flex cp-items-center cp-px-2 cp-py-0.5 cp-rounded-md cp-text-[10px] cp-font-bold cp-uppercase cp-tracking-wider
                                            {{ $ticket->priority === 'high' ? 'cp-bg-rose-500/10 cp-text-rose-500' : '' }}
                                            {{ $ticket->priority === 'medium' ? 'cp-bg-amber-500/10 cp-text-amber-500' : '' }}
                                            {{ $ticket->priority === 'low' ? 'cp-bg-emerald-500/10 cp-text-emerald-500' : '' }}">
                                            {{ $ticket->priority }} Priority
                                        </span>
                                    </div>
                                    <h4 class="cp-text-base cp-font-bold cp-text-gray-900 dark:cp-text-white">{{ $ticket->subject }}</h4>
                                    <p class="cp-text-xs cp-text-gray-400 dark:cp-text-slate-500">Category: <span class="cp-font-semibold cp-capitalize">{{ $ticket->category }}</span> &bull; Submitted: {{ $ticket->created_at->diffForHumans() }}</p>
                                </div>
                                <div class="cp-flex cp-items-center cp-gap-2">
                                    <button wire:click="viewTicket({{ $ticket->id }})"
                                        class="cp-px-4 cp-py-2 cp-bg-gray-50 dark:cp-bg-white/5 hover:cp-bg-gray-100 dark:hover:cp-bg-white/10 cp-text-gray-700 dark:cp-text-slate-300 cp-text-xs cp-font-bold cp-rounded-xl cp-transition-all">
                                        Details
                                    </button>
                                    @if(in_array($ticket->status, ['open', 'in_progress']))
                                        <button wire:click="editTicket({{ $ticket->id }})"
                                            class="cp-p-2 cp-bg-indigo-50 dark:cp-bg-indigo-950/30 hover:cp-bg-indigo-100 dark:hover:cp-bg-indigo-950/50 cp-text-indigo-600 dark:cp-text-indigo-400 cp-text-xs cp-font-bold cp-rounded-xl cp-transition-all"
                                            title="Edit Ticket">
                                            <svg class="cp-w-4 cp-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <button wire:click="closeTicket({{ $ticket->id }})"
                                            class="cp-p-2 cp-bg-rose-50 dark:cp-bg-rose-950/30 hover:cp-bg-rose-100 dark:hover:cp-bg-rose-950/50 cp-text-rose-600 dark:cp-text-rose-450 cp-text-xs cp-font-bold cp-rounded-xl cp-transition-all"
                                            title="Close Ticket">
                                            <svg class="cp-w-4 cp-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        {{-- Form to Create Ticket --}}
        @if($showForm)
            <div class="cp-max-w-3xl cp-mx-auto cp-bg-white dark:cp-bg-slate-900/60 cp-border cp-border-gray-100 dark:cp-border-white/5 cp-rounded-3xl cp-shadow-xl cp-p-6">
                <div class="cp-flex cp-justify-between cp-items-center cp-mb-6">
                    <h3 class="cp-text-lg cp-font-bold cp-text-gray-900 dark:cp-text-white">{{ $editingTicketId ? 'Update Support Ticket' : 'Submit New Support Ticket' }}</h3>
                    <button wire:click="toggleForm" class="cp-p-1.5 cp-bg-gray-50 dark:cp-bg-white/5 cp-rounded-xl cp-text-gray-400 hover:cp-text-gray-600 dark:hover:cp-text-white">
                        <svg class="cp-w-5 cp-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit.prevent="submit" class="cp-space-y-5">
                    <div class="cp-grid cp-grid-cols-1 md:cp-grid-cols-2 cp-gap-5">
                        
                        {{-- Category --}}
                        <div>
                            <label for="ticket_category" class="cp-text-xs cp-font-bold cp-text-gray-500 dark:cp-text-slate-400 cp-uppercase cp-tracking-wider cp-block cp-mb-2">Category</label>
                            <select id="ticket_category" wire:model="category"
                                class="cp-w-full cp-px-4 cp-py-3 cp-bg-gray-50 dark:cp-bg-slate-950 cp-border cp-border-gray-200 dark:cp-border-white/10 cp-rounded-2xl cp-text-sm cp-text-gray-900 dark:cp-text-white focus:cp-outline-none focus:cp-ring-2 focus:cp-ring-indigo-500/20 focus:cp-border-indigo-500 cp-transition-all">
                                <option value="connection">Connection Issue</option>
                                <option value="speed">Slow Speed</option>
                                <option value="billing">Billing Inquiry</option>
                                <option value="other">Other Inquiry</option>
                            </select>
                            @error('category') <p class="cp-text-xs cp-text-rose-500 cp-mt-1.5">{{ $message }}</p> @enderror
                        </div>

                        {{-- Priority --}}
                        <div>
                            <label for="ticket_priority" class="cp-text-xs cp-font-bold cp-text-gray-500 dark:cp-text-slate-400 cp-uppercase cp-tracking-wider cp-block cp-mb-2">Priority</label>
                            <select id="ticket_priority" wire:model="priority"
                                class="cp-w-full cp-px-4 cp-py-3 cp-bg-gray-50 dark:cp-bg-slate-950 cp-border cp-border-gray-200 dark:cp-border-white/10 cp-rounded-2xl cp-text-sm cp-text-gray-900 dark:cp-text-white focus:cp-outline-none focus:cp-ring-2 focus:cp-ring-indigo-500/20 focus:cp-border-indigo-500 cp-transition-all">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                            @error('priority') <p class="cp-text-xs cp-text-rose-500 cp-mt-1.5">{{ $message }}</p> @enderror
                        </div>

                    </div>

                    {{-- Subject --}}
                    <div>
                        <label for="ticket_subject" class="cp-text-xs cp-font-bold cp-text-gray-500 dark:cp-text-slate-400 cp-uppercase cp-tracking-wider cp-block cp-mb-2">Subject</label>
                        <input id="ticket_subject" type="text" wire:model="subject" placeholder="Summarize your issue..."
                            class="cp-w-full cp-px-4 cp-py-3 cp-bg-gray-50 dark:cp-bg-slate-950 cp-border cp-border-gray-200 dark:cp-border-white/10 cp-rounded-2xl cp-text-sm cp-text-gray-900 dark:cp-text-white placeholder-gray-400 focus:cp-outline-none focus:cp-ring-2 focus:cp-ring-indigo-500/20 focus:cp-border-indigo-500 cp-transition-all">
                        @error('subject') <p class="cp-text-xs cp-text-rose-500 cp-mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="ticket_description" class="cp-text-xs cp-font-bold cp-text-gray-500 dark:cp-text-slate-400 cp-uppercase cp-tracking-wider cp-block cp-mb-2">Description</label>
                        <textarea id="ticket_description" wire:model="description" rows="5" placeholder="Explain the problem in detail..."
                            class="cp-w-full cp-px-4 cp-py-3 cp-bg-gray-50 dark:cp-bg-slate-950 cp-border cp-border-gray-200 dark:cp-border-white/10 cp-rounded-2xl cp-text-sm cp-text-gray-900 dark:cp-text-white placeholder-gray-400 focus:cp-outline-none focus:cp-ring-2 focus:cp-ring-indigo-500/20 focus:cp-border-indigo-500 cp-transition-all"></textarea>
                        @error('description') <p class="cp-text-xs cp-text-rose-500 cp-mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    {{-- Action Buttons --}}
                    <div class="cp-flex cp-items-center cp-justify-end cp-gap-3 cp-pt-2">
                        <button type="button" wire:click="toggleForm"
                            class="cp-px-5 cp-py-3 cp-bg-gray-50 dark:cp-bg-white/5 hover:cp-bg-gray-100 dark:hover:cp-bg-white/10 cp-text-gray-700 dark:cp-text-slate-300 cp-font-bold cp-rounded-2xl cp-transition-colors">
                            Cancel
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="cp-px-6 cp-py-3 cp-bg-indigo-600 hover:cp-bg-indigo-500 cp-text-white cp-font-bold cp-rounded-2xl cp-shadow-lg cp-transition-colors cp-flex cp-items-center cp-gap-2">
                            <span wire:loading.remove>{{ $editingTicketId ? 'Update Ticket' : 'Submit Ticket' }}</span>
                            <span wire:loading class="cp-inline-block cp-w-4 cp-h-4 cp-border-2 cp-border-white/30 cp-border-t-white cp-rounded-full cp-animate-spin"></span>
                            <span wire:loading>{{ $editingTicketId ? 'Updating...' : 'Submitting...' }}</span>
                        </button>
                    </div>

                </form>
            </div>
        @endif

        {{-- Details View --}}
        @if($viewingTicketId && ($ticket = $this->getViewingTicket()))
            <div class="cp-max-w-3xl cp-cp-mx-auto cp-bg-white dark:cp-bg-slate-900/60 cp-border cp-border-gray-100 dark:cp-border-white/5 cp-rounded-3xl cp-shadow-xl cp-p-6">
                
                {{-- Header --}}
                <div class="cp-flex cp-flex-col sm:cp-flex-row cp-justify-between cp-items-start sm:cp-items-center cp-gap-4 cp-mb-6 cp-pb-4 cp-border-b cp-border-gray-100 dark:cp-border-white/5">
                    <div>
                        <div class="cp-flex cp-items-center cp-gap-2.5 cp-mb-1">
                            <span class="cp-text-xs cp-font-bold cp-text-indigo-500 dark:cp-text-indigo-400">{{ $ticket->ticket_no }}</span>
                            <span class="cp-inline-flex cp-items-center cp-px-2 cp-py-0.5 cp-rounded-md cp-text-[10px] cp-font-bold cp-uppercase cp-tracking-wider
                                {{ $ticket->status === 'open' ? 'cp-bg-amber-500/10 cp-text-amber-500' : '' }}
                                {{ $ticket->status === 'in_progress' ? 'cp-bg-blue-500/10 cp-text-blue-500' : '' }}
                                {{ $ticket->status === 'resolved' ? 'cp-bg-emerald-500/10 cp-text-emerald-500' : '' }}
                                {{ $ticket->status === 'closed' ? 'cp-bg-gray-500/10 cp-text-gray-500' : '' }}">
                                {{ str_replace('_', ' ', $ticket->status) }}
                            </span>
                        </div>
                        <h3 class="cp-text-lg cp-font-bold cp-text-gray-900 dark:cp-text-white">{{ $ticket->subject }}</h3>
                        <p class="cp-text-xs cp-text-gray-400 dark:cp-text-slate-500">Category: <span class="cp-font-semibold cp-capitalize">{{ $ticket->category }}</span> &bull; Submitted: {{ $ticket->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div class="cp-flex cp-flex-wrap cp-items-center cp-gap-2.5">
                        @if(in_array($ticket->status, ['open', 'in_progress']))
                            <button wire:click="editTicket({{ $ticket->id }})"
                                class="cp-flex cp-items-center cp-gap-1.5 cp-px-4 cp-py-2 cp-bg-indigo-50 dark:cp-bg-indigo-950/30 hover:cp-bg-indigo-100 dark:hover:cp-bg-indigo-950/50 cp-text-indigo-600 dark:cp-text-indigo-400 cp-text-xs cp-font-bold cp-rounded-xl cp-transition-colors">
                                <svg class="cp-w-4 cp-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </button>
                            <button wire:click="closeTicket({{ $ticket->id }})"
                                class="cp-flex cp-items-center cp-gap-1.5 cp-px-4 cp-py-2 cp-bg-rose-50 dark:cp-bg-rose-950/30 hover:cp-bg-rose-100 dark:hover:cp-bg-rose-950/50 cp-text-rose-600 dark:cp-text-rose-450 cp-text-xs cp-font-bold cp-rounded-xl cp-transition-colors">
                                <svg class="cp-w-4 cp-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                Close Ticket
                            </button>
                        @endif
                        <button wire:click="closeView"
                            class="cp-flex cp-items-center cp-gap-2 cp-px-4 cp-py-2 cp-bg-gray-50 dark:cp-bg-white/5 hover:cp-bg-gray-100 dark:hover:cp-bg-white/10 cp-text-gray-700 dark:cp-text-slate-300 cp-text-xs cp-font-bold cp-rounded-xl cp-transition-colors">
                            <svg class="cp-w-4 cp-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            Back to List
                        </button>
                    </div>
                </div>

                {{-- Content --}}
                <div class="cp-space-y-6">
                    
                    {{-- Description --}}
                    <div class="cp-space-y-2">
                        <h4 class="cp-text-xs cp-font-bold cp-text-gray-400 dark:cp-text-slate-500 cp-uppercase cp-tracking-wider">Problem Description</h4>
                        <div class="cp-p-4 cp-bg-gray-50 dark:cp-bg-slate-950 cp-rounded-2xl cp-text-sm cp-text-gray-800 dark:cp-text-slate-300 cp-whitespace-pre-line">
                            {{ $ticket->description }}
                        </div>
                    </div>

                    {{-- Admin Reply --}}
                    @if($ticket->admin_reply)
                        <div class="cp-space-y-2 cp-pt-4 cp-border-t cp-border-gray-100 dark:cp-border-white/5">
                            <div class="cp-flex cp-justify-between cp-items-center">
                                <h4 class="cp-text-xs cp-font-bold cp-text-emerald-500 cp-uppercase cp-tracking-wider">Reply from Support Team</h4>
                                <span class="cp-text-[10px] cp-text-gray-400 dark:cp-text-slate-500">{{ $ticket->replied_at ? $ticket->replied_at->format('M d, Y H:i') : '' }}</span>
                            </div>
                            <div class="cp-p-4 cp-bg-emerald-500/5 cp-border cp-border-emerald-500/10 cp-rounded-2xl cp-text-sm cp-text-gray-800 dark:cp-text-slate-300 cp-whitespace-pre-line">
                                {{ $ticket->admin_reply }}
                            </div>
                        </div>
                    @else
                        <div class="cp-p-4 cp-bg-amber-500/5 cp-border cp-border-amber-500/10 cp-rounded-2xl cp-text-xs cp-text-amber-600 dark:cp-text-amber-400 cp-flex cp-items-start cp-gap-2">
                            <svg class="cp-w-4 cp-h-4 cp-shrink-0 cp-mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span>Awaiting response from our support team. Tickets are usually replied to within a few hours.</span>
                        </div>
                    @endif

                </div>

            </div>
        @endif

        @endif
    </div>
</x-filament-panels::page>
