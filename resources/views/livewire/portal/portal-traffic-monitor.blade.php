<div class="cp-zoom-in">
    <div class="cp-grid cp-grid-cols-1 cp-gap-4">
        <!-- Dashboard Scorecards -->
        <div class="cp-grid cp-grid-cols-1 md:cp-grid-cols-2 cp-gap-4 cp-mb-4">
            <div
                class="cp-bg-white dark:cp-bg-gray-800 cp-p-6 cp-rounded-2xl cp-shadow-sm cp-border cp-border-gray-100 dark:cp-border-gray-700 cp-flex cp-items-center cp-gap-6">
                <div class="cp-p-4 cp-rounded-full cp-bg-green/10 cp-text-green cp-ring-8 cp-ring-green/5">
                    <svg class="cp-w-10 cp-h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75l3 3m0 0l3-3m-3 3v-7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="cp-text-xs cp-uppercase cp-tracking-wider cp-font-semibold cp-text-gray-400 cp-mb-1">
                        {{ __('Download Speed') }}</p>
                    <h3 class="cp-text-3xl cp-font-extrabold cp-text-gray-900 dark:cp-text-white" id="rx-label">0.00
                        Mbps</h3>
                </div>
            </div>
            <div
                class="cp-bg-white dark:cp-bg-gray-800 cp-p-6 cp-rounded-2xl cp-shadow-sm cp-border cp-border-gray-100 dark:cp-border-gray-700 cp-flex cp-items-center cp-gap-6">
                <div class="cp-p-4 cp-rounded-full cp-bg-red/10 cp-text-red cp-ring-8 cp-ring-red/5">
                    <svg class="cp-w-10 cp-h-10" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 11.25l-3-3m0 0l-3 3m3-3v7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="cp-text-xs cp-uppercase cp-tracking-wider cp-font-semibold cp-text-gray-400 cp-mb-1">
                        {{ __('Upload Speed') }}</p>
                    <h3 class="cp-text-3xl cp-font-extrabold cp-text-gray-900 dark:cp-text-white" id="tx-label">0.00
                        Mbps</h3>
                </div>
            </div>
        </div>

        <div class="cp-w-full">
            <div
                class="cp-bg-white dark:cp-bg-gray-800 cp-rounded-xl cp-shadow-sm cp-border cp-border-gray-100 dark:cp-border-gray-700 cp-h-100">
                <div
                    class="cp-p-4 cp-border-b cp-border-gray-100 dark:cp-border-gray-700 cp-flex cp-justify-between cp-items-center">
                    <span class="cp-font-bold cp-text-gray-900 dark:cp-text-white cp-flex cp-items-center cp-gap-3">
                        <div class="cp-bg-gray-100 dark:cp-bg-gray-700 cp-p-2 cp-rounded-lg">
                            <svg class="cp-w-8 cp-h-8 cp-text-indigo-500 cp-animate-pulse" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <span class="cp-text-lg">{{ __('Real-time Performance') }}</span>
                    </span>
                    <div class="cp-flex cp-items-center cp-gap-2">
                        <div class="cp-flex cp-h-2 cp-w-2 cp-relative">
                            <span
                                class="cp-animate-ping cp-absolute cp-inline-flex cp-h-full cp-w-full cp-rounded-full cp-bg-green-400 cp-opacity-75"></span>
                            <span
                                class="cp-relative cp-inline-flex cp-rounded-full cp-h-2 cp-w-2 cp-bg-green-500"></span>
                        </div>
                        <span class="cp-text-xs cp-font-bold cp-text-green-500 cp-uppercase cp-tracking-tighter">{{ __('Live Monitor') }}</span>
                    </div>
                </div>
                <div class="cp-p-4 cp-pt-0">
                    @if (!$selectedRouter || !$selectedInterface)
                        <div class="cp-flex cp-flex-col cp-items-center cp-justify-center cp-py-20 cp-text-gray-400">
                            <svg class="cp-w-12 cp-h-12 cp-mb-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p>{{ __('No active router or session detected. Re-connect to monitor traffic.') }}</p>
                        </div>
                    @else
                        <!-- Polling trigger - increased interval to 3s to reduce 500 errors -->
                        <div wire:poll.3000ms="poll" class="cp-hidden"></div>

                        <div wire:ignore x-data="{
                            chart: null,
                            dataRx: [],
                            dataTx: []
                        }" x-init="const renderChart = () => {
                            let now = new Date().getTime();
                            if ($data.dataRx.length === 0) {
                                for (let i = 60; i > 0; i--) {
                                    let ts = now - (i * 2000);
                                    $data.dataRx.push([ts, 0]);
                                    $data.dataTx.push([ts, 0]);
                                }
                            }
                        
                            const isDark = document.documentElement.classList.contains('dark');
                            const options = {
                                series: [
                                    { name: '{{ __('Download') }}', data: $data.dataRx },
                                    { name: '{{ __('Upload') }}', data: $data.dataTx }
                                ],
                                chart: {
                                    type: 'area',
                                    height: 350,
                                    animations: { enabled: true, easing: 'linear', dynamicAnimation: { speed: 2000 } },
                                    toolbar: { show: false },
                                    zoom: { enabled: false }
                                },
                                theme: {
                                    mode: isDark ? 'dark' : 'light'
                                },
                                colors: ['#22c55e', '#ef4444'],
                                stroke: { curve: 'smooth', width: 3 },
                                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05 } },
                                markers: { size: 0, hover: { size: 5 } },
                                xaxis: {
                                    type: 'datetime',
                                    labels: { show: true, datetimeUTC: false, format: 'HH:mm:ss' }
                                },
                                yaxis: {
                                    labels: { 
                                        formatter: (v) => {
                                            if (v >= 1) return v.toFixed(1) + ' Mbps';
                                            return (v * 1024).toFixed(0) + ' Kbps';
                                        }
                                    },
                                    min: 0,
                                    max: (max) => max < 0.2 ? 0.5 : (max < 1 ? 1 : (max < 5 ? 5 : max + 2))
                                },
                                dataLabels: {
                                    enabled: true,
                                    formatter: (v) => {
                                        if (v >= 1) return v.toFixed(1);
                                        if (v > 0.001) return (v * 1024).toFixed(0) + 'K';
                                        return '';
                                    },
                                    style: { fontSize: '10px', colors: ['#22c55e', '#ef4444'] },
                                    background: { enabled: true, borderWidth: 0, opacity: 0.7 }
                                },
                                legend: { position: 'top' },
                                tooltip: { 
                                    x: { format: 'HH:mm:ss' },
                                    y: {
                                        formatter: (val) => {
                                            if (val >= 1) return val.toFixed(2) + ' Mbps';
                                            return (val * 1024).toFixed(1) + ' Kbps';
                                        }
                                    }
                                }
                             };
                        
                            if ($data.chart) $data.chart.destroy();
                            $data.chart = new ApexCharts(document.querySelector('#portal-traffic-chart'), options);
                            $data.chart.render();

                            // Live theme observer
                            if (window.portalTrafficThemeObserver) window.portalTrafficThemeObserver.disconnect();
                            window.portalTrafficThemeObserver = new MutationObserver((mutations) => {
                                mutations.forEach((mutation) => {
                                    if (mutation.attributeName === 'class') {
                                        const activeDark = document.documentElement.classList.contains('dark');
                                        if ($data.chart) {
                                            $data.chart.updateOptions({
                                                theme: { mode: activeDark ? 'dark' : 'light' }
                                            });
                                        }
                                    }
                                });
                            });
                            window.portalTrafficThemeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
                         };
                        
                         // Relies on ApexCharts being available globally via NPM bundle (app.js)
                         $nextTick(() => {
                             if (window.ApexCharts) {
                                 renderChart();
                             }
                         });
                        
                         window.addEventListener('traffic-updated', (e) => {
                             let evt = Array.isArray(e.detail) ? e.detail[0] : e.detail;
                             let rxBytes = evt.rx || 0;
                             let txBytes = evt.tx || 0;
                             let rxMbps = rxBytes / 1048576;
                             let txMbps = txBytes / 1048576;
                        
                             const formatLabel = (bytes) => {
                                 if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' Mbps';
                                 if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' Kbps';
                                 return bytes.toFixed(0) + ' bps';
                             };
                        
                             document.getElementById('rx-label').innerText = formatLabel(rxBytes);
                             document.getElementById('tx-label').innerText = formatLabel(txBytes);
                        
                             let now = new Date().getTime();
                             $data.dataRx.push([now, rxMbps]);
                             $data.dataTx.push([now, txMbps]);
                             if ($data.dataRx.length > 900) $data.dataRx.shift();
                             if ($data.dataTx.length > 900) $data.dataTx.shift();
                        
                             if ($data.chart) $data.chart.updateSeries([{ data: $data.dataRx }, { data: $data.dataTx }], true);
                         });">
                            <div id="portal-traffic-chart" style="width: 100%; height: 350px;"></div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
