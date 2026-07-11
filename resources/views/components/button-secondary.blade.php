<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-secondary btn-sm']) }}>
    {{ $slot }}
</button>
