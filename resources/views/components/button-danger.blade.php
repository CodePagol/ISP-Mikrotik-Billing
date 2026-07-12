<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-danger btn-sm']) }}>
    {{ $slot }}
</button>
