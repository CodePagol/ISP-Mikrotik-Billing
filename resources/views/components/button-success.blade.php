<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-success btn-sm']) }}>
    {{ $slot }}
</button>