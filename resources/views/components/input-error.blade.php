@props(['for'])

@error($for)
    <span {{ $attributes->merge(['class' => 'text-danger small']) }}>{{ $message }}</span>
@enderror
