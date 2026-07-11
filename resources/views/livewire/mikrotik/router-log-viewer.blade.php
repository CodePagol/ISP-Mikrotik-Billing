<div>
    <div class="container-fluid py-4 zoom-in">

        {{-- Page Header --}}
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 bg-white">
                    <div class="card-body py-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="p-2 rounded-3 bg-success-subtle text-success">
                                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                                </svg>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">{{ __('Router Log Viewer') }}</h5>
                                <small class="text-muted">
                                    {{ __('Live logs from MikroTik') }} &mdash;
                                    @if($logServerEnabled)
                                        <span class="badge bg-success-subtle text-success border border-success">{{ __('Log Server: ON') }}</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary">{{ __('Log Server: OFF') }}</span>
                                    @endif
                                </small>
                            </div>
                        </div>
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="autoRefreshToggle" wire:model.live="autoRefresh">
                                <label class="form-check-label small fw-semibold text-muted" for="autoRefreshToggle">{{ __('Auto-refresh') }}</label>
                            </div>
                            <button class="btn btn-sm btn-outline-success rounded-3 px-3 py-1.5" wire:click="pollLogs" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="pollLogs">
                                    <i class="bi bi-arrow-clockwise me-1"></i>{{ __('Refresh') }}
                                </span>
                                <span wire:loading wire:target="pollLogs">
                                    <span class="spinner-border spinner-border-sm me-1"></span>{{ __('Fetching...') }}
                                </span>
                            </button>
                            @if($logServerEnabled)
                            <button class="btn btn-sm btn-outline-danger rounded-3 px-3 py-1.5" wire:click="clearOldLogs" wire:confirm="{{ __('Delete old logs based on retention policy?') }}">
                                <i class="bi bi-trash me-1"></i>{{ __('Prune Logs') }}
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters Bar --}}
        <div class="row g-2 mb-4">
            <div class="col-md-3 col-sm-6 col-12">
                <select class="form-select form-select-sm shadow-xs" wire:model.live="selectedRouter" style="border-radius: 6px;">
                    <option value="">{{ __('— All Routers —') }}</option>
                    @foreach($routers as $router)
                        <option value="{{ $router }}">{{ $router }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 col-sm-6 col-12">
                <select class="form-select form-select-sm shadow-xs" wire:model.live="filterTopic" style="border-radius: 6px;">
                    <option value="">{{ __('— All Topics —') }}</option>
                    <option value="info">{{ __('Info') }}</option>
                    <option value="warning">{{ __('Warning') }}</option>
                    <option value="error">{{ __('Error') }}</option>
                    <option value="critical">{{ __('Critical') }}</option>
                    <option value="firewall">{{ __('Firewall') }}</option>
                    <option value="ppp">{{ __('PPP') }}</option>
                    <option value="account">{{ __('Account') }}</option>
                    <option value="dhcp">{{ __('DHCP') }}</option>
                    <option value="system">{{ __('System') }}</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-6 col-12">
                <select class="form-select form-select-sm shadow-xs" wire:model.live="filterBuffer" style="border-radius: 6px;">
                    <option value="">{{ __('— All Buffers —') }}</option>
                    <option value="memory">{{ __('Memory') }}</option>
                    <option value="disk">{{ __('Disk') }}</option>
                    <option value="remote">{{ __('Remote') }}</option>
                </select>
            </div>
            <div class="col-md-5 col-sm-6 col-12">
                <div class="input-group input-group-sm shadow-xs">
                    <span class="input-group-text bg-white text-muted border-end-0">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" placeholder="{{ __('Search message...') }}" wire:model.live.debounce.500ms="searchMessage" style="border-radius: 6px;">
                </div>
            </div>
        </div>

        {{-- Log Table --}}
        @if($logServerEnabled)
        <div class="card border-0 shadow-sm rounded-3 bg-white">
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 70vh; overflow-y:auto;">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="table-success text-success sticky-top">
                            <tr class="fw-semibold">
                                <th style="width:160px" class="ps-4 py-3">{{ __('Time') }}</th>
                                <th style="width:100px">{{ __('Router') }}</th>
                                <th style="width:120px">{{ __('Topics') }}</th>
                                <th style="width:80px">{{ __('Buffer') }}</th>
                                <th>{{ __('Message') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                @php
                                    $color = match(true) {
                                        str_contains($log->topics, 'error') || str_contains($log->topics, 'critical') => 'danger',
                                        str_contains($log->topics, 'warning') => 'warning',
                                        str_contains($log->topics, 'firewall') => 'warning',
                                        str_contains($log->topics, 'ppp') || str_contains($log->topics, 'account') => 'success',
                                        default => 'secondary'
                                    };
                                @endphp
                                <tr>
                                    <td class="text-muted text-nowrap ps-4">
                                        <span class="font-monospace fw-semibold text-dark">{{ $log->time ?? $log->created_at->format('H:i:s') }}</span><br>
                                        <span class="x-small text-muted" style="font-size:0.75rem;">{{ $log->created_at->format('Y-m-d') }}</span>
                                    </td>
                                    <td><span class="badge bg-secondary-subtle text-secondary border border-secondary">{{ $log->router_name }}</span></td>
                                    <td><span class="badge bg-{{ $color }}-subtle text-{{ $color }} border border-{{ $color }}-subtle text-truncate" style="max-width:110px" title="{{ $log->topics }}">{{ $log->topics }}</span></td>
                                    <td><span class="badge bg-light text-muted border">{{ $log->buffer ?? 'memory' }}</span></td>
                                    <td class="font-monospace text-break text-dark">{{ $log->message }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-journal-x fs-2 text-success d-block mb-2"></i>
                                        <p class="mb-1 fw-bold">{{ __('No stored logs yet.') }}</p>
                                        <small class="text-muted">{!! __('Click :refresh to fetch and store logs from the router.', ['refresh' => '<strong>' . __('Refresh') . '</strong>']) !!}</small>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($logs->hasPages())
            <div class="px-4 py-3 border-top bg-light">
                {{ $logs->links() }}
            </div>
            @endif
        </div>
        @else
        {{-- Log server is disabled: show live log stream only --}}
        <div class="card border-0 shadow-sm rounded-3 bg-white">
            <div class="card-body">
                <div class="alert alert-warning d-flex align-items-center gap-2 mb-3 border-warning-subtle" style="font-size: 0.88rem;">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div>
                        <strong>{{ __('Log Server is disabled.') }}</strong> {{ __('Logs are shown live only — not stored to the database.') }}
                        {!! __('Go to :settings to enable log storage.', ['settings' => '<a href="' . route('site-settings') . '" wire:navigate>' . __('Site Settings') . '</a>']) !!}
                    </div>
                </div>

                {{-- Live stream terminal --}}
                <div id="log-terminal" class="bg-dark text-success rounded-3 p-3 font-monospace overflow-auto shadow-inner" style="height:65vh; font-size:0.8rem; line-height: 1.5;"
                    wire:ignore
                    x-data="{ lines: [] }"
                    x-init="
                        window.addEventListener('logs-refreshed', (e) => {
                            let entries = e.detail[0]?.logs || e.detail?.logs || [];
                            if (!Array.isArray(entries)) {
                                if (Array.isArray(e.detail)) entries = e.detail;
                                else if (Array.isArray(e.detail[0])) entries = e.detail[0];
                                else entries = Object.values(entries || {});
                            }
                            
                            (Array.isArray(entries) ? entries : []).forEach(line => {
                                let color = 'text-success';
                                const t = (line.topics || '').toLowerCase();
                                if (t.includes('error') || t.includes('critical')) color = 'text-danger';
                                else if (t.includes('warning') || t.includes('firewall')) color = 'text-warning';
                                else if (t.includes('info')) color = 'text-info';
                                lines.push({ ...line, color });
                            });
                            if (lines.length > 800) lines = lines.slice(-800);
                            $nextTick(() => { $el.scrollTop = $el.scrollHeight; });
                        });
                    ">
                    <template x-for="(line, i) in lines" :key="i">
                        <div :class="line.color" class="mb-0 lh-sm py-0.5">
                            <span class="text-muted me-2" x-text="line.time || ''"></span>
                            <span class="badge bg-secondary me-1" style="font-size: 0.65rem;" x-text="line.buffer || 'memory'"></span>
                            <span class="fw-bold me-2" x-text="'[' + (line.topics || '') + ']'"></span>
                            <span class="text-white-50" x-text="line.message || ''"></span>
                        </div>
                    </template>
                    <div x-show="lines.length === 0" class="text-secondary text-center pt-5">
                        <p>{!! __('Waiting for logs... click :refresh or select a router.', ['refresh' => '<strong>' . __('Refresh') . '</strong>']) !!}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Auto-poll --}}
        @if($autoRefresh && $selectedRouter)
        <div wire:poll.10000ms="pollLogs" class="d-none"></div>
        @endif
    </div>
</div>
