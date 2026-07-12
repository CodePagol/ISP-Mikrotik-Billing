{{-- <style>
    /* .checkbox-on-off */
.checkbox-on-off {
  width: 80px;
  height: 26px;
  background: #333;
  margin: 20px auto;
  position: relative;
  border-radius: 50px;
  box-shadow: inset 0px 1px 1px rgba(0,0,0,0.5), 0px 1px 0px rgba(255,255,255,0.2);
  &:after {
    content: 'OFF';
    color: #000;
    position: absolute;
    right: 10px;
    z-index: 0;
    font: 12px/26px Arial, sans-serif;
    font-weight: bold;
    text-shadow: 1px 1px 0px rgba(255,255,255,.15);
  }
  &:before {
    content: 'ON';
    color: #27ae60;
    position: absolute;
    left: 10px;
    z-index: 0;
    font: 12px/26px Arial, sans-serif;
    font-weight: bold;
  }
  label {
    display: block;
    width: 34px;
    height: 20px;
    cursor: pointer;
    position: absolute;
    top: 3px;
    left: 3px;
    z-index: 1;
    background: #fcfff4;
    background: linear-gradient(top, #fcfff4 0%, #dfe5d7 40%, #b3bead 100%);
    border-radius: 50px;
    transition: all 0.4s ease;
    box-shadow: 0px 2px 5px 0px rgba(0,0,0,0.3);
  }
  input[type=checkbox] {
    visibility: hidden;
    &:checked + label {
      left: 43px;
    }
  }
}
/* end .checkbox-on-off */
</style> --}}

<div class="form-group {{ $column ?? 'input-group-sm col-md-6 col-sm-12' }}" style="{{ $groupstyle ?? '' }}">
    <label for="{{ $name }}" class="form-label {{$labelClass ?? ''}}">
        {{ __($label ?? '') }} <!-- Label text -->
        @if($required ?? false)
            <span style="color: red;">*</span> <!-- Red asterisk for required fields -->
        @endif
    </label>
    @if ($type == 'text')
        <input type="text" placeholder="{{ __($placeholder ?? $label) }}" id="{{ $name }}" name="{{ $name }}" value="{{ old($name) }}" class="form-control @error($name) is-invalid @enderror" wire:model="{{$name}}">
        <x-error name='{{ $name }}' />
    @elseif ($type == 'number')
        <input type="number" id="{{ $name }}" placeholder="{{ __($placeholder ?? '00.00') }}" name="{{ $name }}" value="{{ old($name) }}" class="form-control @error($name) is-invalid @enderror" wire:model="{{$name}}" min="0" value="{{ $value ?? '' }}"  @if (isset($wInput) && $wInput != '') wire:input="{{ $wInput }}" @endif @if($readonly ?? false) readonly @endif>
        <x-error name='{{ $name }}' />
    @elseif ($type == 'mobile')
        <div wire:ignore>
            <input type="text" id="{{ $name }}" class="form-control form-control-sm @error($name) is-invalid @enderror" placeholder="{{ __($placeholder ?? $label) }}" name="{{ $name }}" x-data="intlTelInput('{{ $name }}')" value="{{ isset($this->{$name}) ? $this->{$name} : '' }}">
        </div>
        <x-error name='{{ $name }}' />
    @elseif ($type == 'dropdown')
        <select class="form-control @error($name) is-invalid @enderror" name="{{ $name }}" wire:model="{{$name}}" id="{{$name}}" @if (isset($wChange) && $wChange != '') wire:change="{{ $wChange }}" @endif {{ ($multiple ?? false) ? 'multiple' : '' }} style="{{ $inputStyle ?? '' }}">
            @if ($placeholder != '')
                <option value="">{{ __($placeholder ?? $label) }}</option>
            @endif
            @foreach ($options ?? [] as $option)
                <option value="{{ $option }}" >{{ $option }}</option>
            @endforeach
        </select>
        <x-error name='{{ $name }}' />
    @elseif ($type == 'dropdownKey')
        <select class="form-control @error($name) is-invalid @enderror" name="{{ $name }}" wire:model="{{$name}}" id="{{$name}}" @if (isset($wChange) && $wChange != '') wire:change="{{ $wChange }}" @endif {{ ($multiple ?? false) ? 'multiple' : '' }} style="{{ $inputStyle ?? '' }}">
            @if ($placeholder != '')
                <option value="">{{ __($placeholder ?? $label) }}</option>
            @endif
            @foreach ($options ?? [] as $key => $option)
                <option value="{{ $key }}" {{ (isset($selectedValue) && $selectedValue == $key) ? 'selected' : '' }}>{{ $option }}</option>
            @endforeach
        </select>
        <x-error name='{{ $name }}' />
    @elseif ($type == 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" class="form-control h-25 @error($name) is-invalid @enderror" wire:model="{{$name}}">{{ old($name) }}</textarea>
        <x-error name='{{ $name }}' />
    @elseif ($type == 'date')
        <input type="date" id="{{ $name }}" name="{{ $name }}" class="form-control" placeholder="{{ __($label) }}" wire:model="{{$name}}" value="{{ $value ?? '' }}">
        <x-error name='{{ $name }}' />
    @elseif ($type == 'checkbox')
        <div class="form-check form-switch">
            <input class="form-check-input" role="switch" type="checkbox" name="{{ $name }}" value="1" wire:model="{{$name}}" id="{{ $name }}" {{ isset($checked) && $checked ? 'checked' : ''}}>
            <label class="form-check-label" for="{{ $name }}">
                {{ __($checkboxLabel ?? $label ) }}
            </label>
        </div>
        <x-error name='{{ $name }}' />
    @elseif ($type == 'radio')
        <br/>
        @foreach ($options ?? [] as $key => $option)
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="{{ $name }}" value="{{ $key }}" wire:model="{{$name}}" id="{{ $name }}_{{ $key }}" {{ isset($selectedValue) && $selectedValue == $key ? 'checked' : ''}}>
                <label class="form-check-label" for="{{ $name }}_{{ $key }}">{{ $option }}</label>
            </div>
        @endforeach
        <x-error name='{{ $name }}' />
    @elseif ($type == 'file')
        <input type="file" placeholder="{{ __($placeholder ?? $label) }}" id="{{ $name }}" name="{{ $name }}" value="{{ old($name) }}" class="form-control @error($name) is-invalid @enderror" wire:model="{{$name}}">
        <x-error name='{{ $name }}' />
        {{-- <div class="file-preview box sm">
            @if ($photoPreviewUrl)
                <img id="photoPreviewUrl{{ $name }}" src="{{$photoPreviewUrl}}" alt="Image Preview" style="max-width: 300px; max-height: 300px; margin-top: 10px;">
            @endif
        </div> --}}



        {{-- old --}}
    @elseif ($type == 'datetime-local')
        <input type="datetime-local" name="{{ $name }}" value="{{ old($name) }}" class="form-control" placeholder="{{ __($label) }}">
    @elseif ($type == 'url')
        <input type="url" name="{{ $name }}" value="{{ old($name) }}" class="form-control" placeholder="{{ __($label) }} link">
        <small class="text-muted">Use proper link without extra parameter. Don't use short share link/embeded iframe code.</small>
    @elseif ($type == 'products_list')
        <select name="{{ $name }}[]" id="{{ $name }}" class="form-control aiz-selectpicker" multiple required data-placeholder="{{ translate('Choose Product List') }}" data-live-search="true" data-selected-text-format="count">
            @foreach(\App\Models\Product::where('published', 1)->where('approved', 1)->orderBy('created_at', 'desc')->get() as $product)
                <option value="{{$product->id}}">{{ $product->getTranslation('name') }}</option>
            @endforeach
        </select>
        <div class="form-group" id="discount_table"></div>
    @endif
    {{-- <div class="col-md-1 p-0 d-flex">
        <input type="color" name="{{ $name }}_bg_color" value="{{ $bg_color ?? '#ffffff' }}" class="form-control p-1 w-50 bg-info">
        <input type="color" name="{{ $name }}_color" value="{{ $color ?? '#000000' }}" class="form-control p-1 w-50 bg-success">
    </div>
    <div class="col-md-1 checkbox-on-off">
        <input type="checkbox" id="{{ $name }}-checkbox-on-off" name="{{ $name }}_display"  {{ old($name . '_display') ? 'checked' : '' }} checked>
        <label for="{{ $name }}-checkbox-on-off"></label>
    </div> --}}
</div>

{{-- <div class="col-md-6 col-sm-12">
<div class="form-group">
    <label for="{{$name}}">{{$label}}
        {{ $required == "yes" ? '<span style="color: red;">*</span>' : '' }}
    </label>
    <input type="text" class="form-control" id="{{$name}}" wire:model="{{$name}}" placeholder="{{$label}} {{$label_text ?? ''}}">
    @error('{{$name}}') <span class="text-danger">{{ $message }}</span> @enderror
</div>
</div> --}}
