@props(['style_img' => '', 'badge' => '', 'title' => '', 'value' => ''])

<div class="col-sm-3 col-md-2">
    <div class="card overflow-hidden">
        <div class="bg-holder bg-card" style="background-image:url({{asset($style_img)}});"></div>
        <div class="card-body position-relative">
        <h6>{{ $title }}<span class="badge badge-subtle-warning rounded-pill ms-2">{{ $badge  }}</span></h6>
        <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-warning" data-countup="{&quot;endValue&quot;:58.386,&quot;decimalPlaces&quot;:2,&quot;suffix&quot;:&quot;k&quot;}">{{ $value }}</div>
        {{-- <a class="fw-semi-bold fs-10 text-nowrap" href="{{  }}">
            See all
            <span class="bi bi-angle-right ms-1" data-fa-transform="down-1"></span>
        </a> --}}
        </div>
    </div>
</div>
