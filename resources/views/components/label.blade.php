@props(['value'])

<label {{ $attributes->merge(['class' => 'fw-normal text-capitalize']) }}>
    {{ $value ?? $slot }}
</label>
