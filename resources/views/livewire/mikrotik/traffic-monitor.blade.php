<div class="zoom-in">
    <x-slot name="header">{{ __('Live Traffic Monitor') }}</x-slot>

    <div class="row g-3">
        <div class="col-lg-3">
            <div class="card h-100">
                <div class="card-header bg-primary text-white"><i class="bi bi-gear-fill me-1"></i>{{ __('Settings') }}</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">{{ __('1. Select Router') }}</label>
                        <select class="form-select" wire:model.live="selectedRouter">
                            <option value="">{{ __('-- Choose Router --') }}</option>
                            @foreach($routers as $r)
                                <option value="{{ $r->router_name }}">{{ $r->router_name }} ({{ $r->ip_address }})</option>
                            @endforeach
                        </select>
                    </div>

                    @if($selectedRouter)
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">{{ __('2. Select Interface / User') }}</label>
                        <select class="form-select shadow-sm" wire:model.live="selectedInterface">
                            <option value="">{{ __('-- Choose Interface --') }}</option>
                            @foreach($interfaces as $iface)
                                <option value="{{ $iface }}">
                                    @if(str_starts_with($iface, '<pppoe-'))
                                        👤 {{ __('User') }}: {{ str_replace(['<pppoe-', '>'], '', $iface) }}
                                    @elseif(str_starts_with($iface, 'ether'))
                                        🌐 {{ __('Port') }}: {{ $iface }}
                                    @else
                                        {{ $iface }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted mt-1 d-block"><i class="bi bi-info-circle"></i> {{ __("Tip: Select a PPPoE interface to monitor a specific user's live traffic.") }}</small>
                    </div>
                    @endif

                    @if($selectedInterface)
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-success"><i class="bi bi-arrow-down-circle-fill me-1"></i>{{ __('Download') }}</span>
                            <strong class="text-success fs-5" id="rx-label">0 Mbps</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-primary"><i class="bi bi-arrow-up-circle-fill me-1"></i>{{ __('Upload') }}</span>
                            <strong class="text-primary fs-5" id="tx-label">0 Mbps</strong>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-activity me-1"></i>{{ __('Real-time Traffic Graph') }} @if($selectedInterface) - <strong>{{ $selectedInterface }}</strong> @endif</span>
                    <span wire:loading wire:target="poll" class="spinner-grow spinner-grow-sm text-success" role="status"></span>
                </div>
                <div class="card-body">
                    @if(!$selectedRouter || !$selectedInterface)
                        <div class="alert alert-info d-flex align-items-center justify-content-center" style="height: 300px;">
                            <div><i class="bi bi-info-circle fs-3 d-block text-center mb-2"></i>{{ __('Please select a router and interface to begin monitoring traffic.') }}</div>
                        </div>
                    @else
                        <!-- Hidden div to trigger polling every 2 seconds -->
                        <div wire:poll.2000ms="poll" class="d-none"></div>
                        <!-- ApexChart container -->
                        <div wire:ignore 
                             x-data="{
                                 chart: null,
                                 dataRx: [],
                                 dataTx: [],
                                 maxPoints: 900,
                                 initialPoints: 60,
                                 initChart() {
                                     if (this.chart) this.chart.destroy();
                                     
                                     this.dataRx = [];
                                     this.dataTx = [];
                                     let now = new Date().getTime();
                                     for(let i = this.initialPoints; i > 0; i--) {
                                         let ts = now - (i * 2000); // Backfill initial 2 mins
                                         this.dataRx.push([ts, 0]);
                                         this.dataTx.push([ts, 0]);
                                     }
                                     const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark' || document.documentElement.classList.contains('dark');
                                     let options = {
                                         series: [
                                             { name: '{{ __('Download') }}', data: this.dataRx },
                                             { name: '{{ __('Upload') }}', data: this.dataTx }
                                         ],
                                         chart: {
                                             type: 'area',
                                             height: 350,
                                             animations: { 
                                                 enabled: true, 
                                                 easing: 'linear', 
                                                 dynamicAnimation: { speed: 2000 } 
                                              },
                                             toolbar: { show: false },
                                             zoom: { enabled: false }
                                         },
                                         theme: {
                                             mode: isDark ? 'dark' : 'light'
                                         },
                                         colors: ['#198754', '#0d6efd'],
                                         dataLabels: { enabled: false },
                                         stroke: { curve: 'smooth', width: 2 },
                                         fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] } },
                                         xaxis: {
                                             type: 'datetime',
                                             labels: { 
                                                 show: true,
                                                 datetimeUTC: false,
                                                 format: 'HH:mm:ss',
                                                 style: { colors: '#94a3b8' }
                                             },
                                             axisBorder: { show: true, color: 'rgba(128,128,128,0.15)' },
                                             axisTicks: { show: true, color: 'rgba(128,128,128,0.15)' }
                                         },
                                         yaxis: { 
                                             labels: { 
                                                 formatter: function (value) { 
                                                     if (value >= 1) return value.toFixed(1) + ' Mbps';
                                                     return (value * 1024).toFixed(0) + ' Kbps';
                                                  } 
                                             },
                                             min: 0,
                                             max: function(max) { return max < 0.2 ? 0.5 : (max < 1 ? 1 : (max < 5 ? 5 : max + 2)); }
                                         },
                                         legend: { position: 'top', horizontalAlign: 'left' },
                                         tooltip: {
                                             x: { format: 'HH:mm:ss' },
                                             y: { 
                                                 formatter: function (val) { 
                                                     if (val >= 1) return val.toFixed(2) + ' Mbps';
                                                     return (val * 1024).toFixed(1) + ' Kbps';
                                                  } 
                                             }
                                         }
                                     };
  
                                     this.chart = new window.ApexCharts(this.$refs.chartContainer, options);
                                     this.chart.render();

                                     // Live theme observer
                                     if (window.adminTrafficThemeObserver) window.adminTrafficThemeObserver.disconnect();
                                     window.adminTrafficThemeObserver = new MutationObserver((mutations) => {
                                         mutations.forEach((mutation) => {
                                             if (mutation.attributeName === 'data-bs-theme' || mutation.attributeName === 'class') {
                                                 const activeDark = document.documentElement.getAttribute('data-bs-theme') === 'dark' || document.documentElement.classList.contains('dark');
                                                 if (this.chart) {
                                                     this.chart.updateOptions({
                                                         theme: { mode: activeDark ? 'dark' : 'light' }
                                                     });
                                                 }
                                             }
                                         });
                                     });
                                     window.adminTrafficThemeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme', 'class'] });
                                 },
                                 updateTraffic(detail) {
                                     if (!this.chart) return;
                                     
                                     let evt = Array.isArray(detail) ? detail[0] : detail;
                                     let rxBytes = evt.rx || 0;
                                     let txBytes = evt.tx || 0;
                                     let rxMbps = rxBytes / 1048576;
                                     let txMbps = txBytes / 1048576;

                                     const formatLabel = (bytes) => {
                                         if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' Mbps';
                                         if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' Kbps';
                                         return bytes.toFixed(0) + ' bps';
                                     };

                                     let rxLabel = document.getElementById('rx-label');
                                     let txLabel = document.getElementById('tx-label');
                                     if (rxLabel) rxLabel.innerText = formatLabel(rxBytes);
                                     if (txLabel) txLabel.innerText = formatLabel(txBytes);

                                     let now = new Date().getTime();
                                     
                                     this.dataRx.push([now, rxMbps]);
                                     if (this.dataRx.length > this.maxPoints) this.dataRx.shift();

                                     this.dataTx.push([now, txMbps]);
                                     if (this.dataTx.length > this.maxPoints) this.dataTx.shift();

                                     this.chart.updateSeries([
                                         { data: this.dataRx },
                                         { data: this.dataTx }
                                     ]);
                                 }
                             }"
                             x-init="
                                 $nextTick(() => {
                                     if (window.ApexCharts) initChart();
                                 });
                             "
                             @traffic-updated.window="updateTraffic($event.detail)"
                             @reset-chart.window="setTimeout(() => { initChart(); }, 100)"
                        >
                            <div x-ref="chartContainer" style="width: 100%; height: 350px;"></div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
