@props(['submit'])

<fieldset class="scheduler-border">
    <legend class="scheduler-border fs-4">{{ $title }}</legend>
    
    <div {{ $attributes->merge(['class' => '']) }}>
        <x-section-title>
            {{-- <x-slot name="title">{{ $title }}</x-slot> --}}
            <x-slot name="description">{{ $description }}</x-slot>
        </x-section-title>

        <div class="">
            <form wire:submit="{{ $submit }}">
                <div class="shadow-sm card-body {{ isset($actions) ? 'rounded-sm' : 'rounded-md' }}">
                    <div class="">
                        {{ $form }}
                    </div>
                    @if (isset($actions))
                        <div class="px-2 py-1 text-end">
                            {{ $actions }}
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>
</fieldset>