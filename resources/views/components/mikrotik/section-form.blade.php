<div class="card border-0 shadow-sm mb-2" style="border-radius: 12px;">
    <div class="card-header bg-white pt-3 pb-2 px-4 position-relative" style="border-bottom: 1px solid rgba(0, 0, 0, 0.065); border-top-left-radius: 12px; border-top-right-radius: 12px;">
        <!-- Modern Left Accent Bar -->
        <div class="position-absolute bg-success" style="width: 4px; left: 0; top: 18px; bottom: 8px; border-radius: 0 4px 4px 0;"></div>
        
        <h5 class="card-title fw-bold text-dark mb-0 ps-2" style="font-size: 1.1rem; letter-spacing: -0.2px;">
            {{ $title ?? '' }}
        </h5>
    </div>
    <div class="card-body px-4 py-3 {{ $class ?? '' }}" {{ $attributes ?? '' }} style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
        {{ $aside ?? '' }}
    </div>
</div>