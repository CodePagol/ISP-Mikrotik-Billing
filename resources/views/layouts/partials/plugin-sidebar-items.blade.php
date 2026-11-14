@php
    $parentTarget = $parent ?? '';
    $pluginItems = \App\Support\PluginNav::getByParent($parentTarget, 'sidebar');
    $isDropdown = $asDropdownItem ?? false;
@endphp
@if(!empty($pluginItems))
    @foreach($pluginItems as $idx => $pItem)
        @php
            $pUrl = \App\Support\PluginNav::resolveUrl($pItem);
            $pIcon = \App\Support\PluginNav::resolveIcon($pItem);
            $pBadge = \App\Support\PluginNav::resolveBadge($pItem);
            $hasChildren = !empty($pItem['children']) && is_array($pItem['children']);
            $dropdownId = 'subItem_' . \Illuminate\Support\Str::slug($pItem['id'] ?? ($pItem['title'] . '_' . $idx));
        @endphp

        @if($isDropdown)
            {{-- Rendering inside an existing <ul> collapse menu (like #mikrotikSetup or #collections) --}}
            <li class="nav-item">
                <a wire:navigate.hover wire:current="active" class="nav-link" href="{{ $pUrl }}">
                    <div class="d-flex align-items-center">
                        @if(!empty($pItem['icon']))
                            <i class="{{ $pIcon }} me-1.5 fs-10"></i>
                        @endif
                        <span class="nav-link-text ps-1">{{ __($pItem['title'] ?? '') }}</span>
                        @if($pBadge)
                            <span class="badge rounded-pill ms-auto {{ $pBadge['class'] }}">{{ $pBadge['text'] }}</span>
                        @endif
                    </div>
                </a>
            </li>
        @else
            {{-- Rendering at section level (like under Customers, Finance, Admin, etc.) --}}
            @if($hasChildren)
                <a class="nav-link dropdown-indicator collapsed" href="#{{ $dropdownId }}" role="button"
                    data-bs-toggle="collapse" aria-expanded="false" aria-controls="{{ $dropdownId }}">
                    <div class="d-flex align-items-center">
                        <span class="nav-link-icon"><i class="{{ $pIcon }}"></i></span>
                        <span class="nav-link-text ps-1">{{ __($pItem['title'] ?? '') }}</span>
                        @if($pBadge)
                            <span class="badge rounded-pill ms-auto {{ $pBadge['class'] }}">{{ $pBadge['text'] }}</span>
                        @endif
                    </div>
                </a>
                <ul class="nav collapse" id="{{ $dropdownId }}">
                    @foreach($pItem['children'] as $child)
                        @php
                            $cUrl = \App\Support\PluginNav::resolveUrl($child);
                            $cIcon = !empty($child['icon']) ? $child['icon'] : null;
                            $cBadge = \App\Support\PluginNav::resolveBadge($child);
                        @endphp
                        <li class="nav-item">
                            <a wire:navigate.hover wire:current="active" class="nav-link" href="{{ $cUrl }}">
                                <div class="d-flex align-items-center">
                                    @if($cIcon)
                                        <i class="{{ $cIcon }} me-1.5 fs-10"></i>
                                    @endif
                                    <span class="nav-link-text ps-1">{{ __($child['title'] ?? '') }}</span>
                                    @if($cBadge)
                                        <span class="badge rounded-pill ms-auto {{ $cBadge['class'] }}">{{ $cBadge['text'] }}</span>
                                    @endif
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <a wire:navigate.hover wire:current="active" class="nav-link" href="{{ $pUrl }}" role="button">
                    <div class="d-flex align-items-center">
                        <span class="nav-link-icon"><i class="{{ $pIcon }}"></i></span>
                        <span class="nav-link-text ps-1">{{ __($pItem['title'] ?? '') }}</span>
                        @if($pBadge)
                            <span class="badge rounded-pill ms-auto {{ $pBadge['class'] }}">{{ $pBadge['text'] }}</span>
                        @endif
                    </div>
                </a>
            @endif
        @endif
    @endforeach
@endif

