<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-light btn-sm']) }}>
    {{ $slot }}
</button>