@props([
    'package',
    'colorClass' => '',
])

<div class="pricing-box {{ $colorClass }} {{ $package->is_featured ? 'pricing-box-featured' : '' }} w-100">
    @php
        $icons = [
            'bi-speedometer2',
            'bi-lightning-charge-fill',
            'bi-rocket-takeoff-fill',
            'bi-cpu',
            'bi-router',
            'bi-wifi',
            'bi-globe',
            'bi-activity',
            'bi-ethernet',
            'bi-cloud-arrow-down-fill'
        ];
        $iconIndex = abs(crc32($package->package ?? '')) % count($icons);
        $selectedIcon = $icons[$iconIndex];

        // Speed fallback detection logic
        $speed = $package->speed;
        if (empty($speed)) {
            if (preg_match('/_(\d+)M$/i', $package->package, $matches)) {
                $speed = $matches[1] . ' Mbps';
            } elseif (is_numeric($package->description)) {
                $speed = $package->description . ' Mbps';
            } else {
                $speed = 'Standard';
            }
        }

        // Clean category name for badge
        $cleanCat = 'Standard';
        if ($package->plan_label && strtolower($package->plan_label) !== 'standard') {
            if (str_contains($package->plan_label, '-')) {
                $cleanCat = explode('-', $package->plan_label)[0];
            } else {
                $cleanCat = $package->plan_label;
            }
        }
        $cleanCat = trim($cleanCat);
    @endphp

    <div class="pricing-head">
        <h4>{{ $package->package }}</h4>

        <div class="pricing-speed-badge">
            <i class="bi {{ $selectedIcon }}"></i>
        </div>
    </div>

    <div class="pricing-lists mb-30">
        <h5>{{ $speed }}</h5>

        <ul class="mt-3">
            @forelse($package->features ?? [] as $feature)
                <li>
                    <i class="bi bi-check-circle-fill"></i>
                    {{ $feature['value'] ?? $feature }}
                </li>
            @empty
                <li><i class="bi bi-check-circle-fill"></i>24 HOURS UNLIMITED</li>
                <li><i class="bi bi-check-circle-fill"></i>Fiber Optics Support</li>
                <li><i class="bi bi-check-circle-fill"></i>24/7 Priority Support</li>
            @endforelse
        </ul>
    </div>

    <div class="price mb-20">
        <h2><span class="price-amount">{{ number_format($package->price) }}৳</span> <span>/MONTH</span></h2>
    </div>

    <div class="pricing-btn mt-auto">
        <a href="javascript:void(0)"
            onclick="Livewire.dispatch('open-purchase-modal',{
                packageName:'{{ addslashes($package->package) }}',
                price:{{ $package->price }}
            })"
            class="price-btn">
            <span>+</span>Buy Package
        </a>
    </div>
</div>