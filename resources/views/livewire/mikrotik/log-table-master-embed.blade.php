<div class="mt-2">
    @php
        $logs = \App\Models\MikrotikLog::latest()->limit(200)->get();
        $logCount = \App\Models\MikrotikLog::count();
    @endphp

    <div class="d-flex align-items-center justify-content-between mb-3 px-1">
        <div>
            <h6 class="mb-0 fw-bold">{{ __('Recent Stored Logs (Latest 200)') }}</h6>
            <small class="text-muted">{{ __('Currently hosting :count records in database', ['count' => number_format($logCount)]) }}</small>
        </div>
        <a href="{{ route('mikrotik-log-viewer') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-arrows-fullscreen me-1"></i> {{ __('Open Advanced DataTables Archive') }}
        </a>
    </div>

    <div class="card shadow-none border overflow-hidden">
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-sm table-hover align-middle mb-0 font-monospace" style="font-size: 0.8rem;">
                <thead class="table-light sticky-top">
                    <tr>
                        <th class="ps-3 py-2">{{ __('Timestamp') }}</th>
                        <th class="py-2">{{ __('Router') }}</th>
                        <th class="py-2">{{ __('Topics') }}</th>
                        <th class="py-2">{{ __('Message') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr class="log-row @if(str_contains(strtolower($log->topics), 'error') || str_contains(strtolower($log->topics), 'critical')) table-danger @elseif(str_contains(strtolower($log->topics), 'warning')) table-warning @endif">
                            <td class="ps-3 text-muted" style="width: 160px; white-space: nowrap;">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="fw-bold text-info" style="width: 120px;">{{ $log->router_name }}</td>
                            <td style="width: 150px;">
                                @foreach(explode(',', $log->topics) as $topic)
                                    <span class="badge bg-light text-dark border p-1" style="font-size: 0.65rem;">{{ trim($topic) }}</span>
                                @endforeach
                            </td>
                            <td class="text-wrap pe-3">
                                {{ $log->message }}
                                @if($log->buffer)
                                    <span class="text-muted small ms-1">({{ $log->buffer }})</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    {{ __('No database logs found. Ensure "Log Server" is enabled and logs are being polled.') }}
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .log-row:hover { background-color: rgba(0,0,0,0.03) !important; }
    .table-responsive::-webkit-scrollbar { width: 6px; }
    .table-responsive::-webkit-scrollbar-track { background: #f1f1f1; }
    .table-responsive::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
    .table-responsive::-webkit-scrollbar-thumb:hover { background: #999; }
</style>
