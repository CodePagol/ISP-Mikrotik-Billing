<div class="container">
    <div {{ $attributes->merge(['class' => 'box']) }}>
        {{ $logo }}

        {{ $slot }}
    </div>
</div>