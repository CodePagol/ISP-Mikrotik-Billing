<fieldset class="scheduler-border">
    <legend class="scheduler-border fs-4">{{ $title }}</legend>
    <div {{ $attributes->merge(['class' => 'shadow-sm p-2']) }}>
        <x-section-title>
            {{-- <x-slot name="title">{{ $title }}</x-slot> --}}
            <x-slot name="description">{{ $description }}</x-slot>
        </x-section-title>


        <div class="mt-3 card-body">
            <div class="px-1 py-2">
                {{ $content }}
            </div>
        </div>
    </div>
</fieldset>