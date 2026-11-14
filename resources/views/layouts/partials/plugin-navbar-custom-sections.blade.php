@php
    $customSections = \App\Support\PluginNav::getCustomSections('navbar');
@endphp
@if(!empty($customSections))
    @foreach($customSections as $sectionTitle => $items)
        @php
            $dropdownId = 'topNavSection_' . \Illuminate\Support\Str::slug($sectionTitle);
        @endphp
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false" id="{{ $dropdownId }}">
                {{ __($sectionTitle) }}
            </a>
            <div class="dropdown-menu dropdown-caret dropdown-menu-card border-0 mt-0" aria-labelledby="{{ $dropdownId }}">
                <div class="bg-white dark__bg-1000 rounded-3 py-2">
                    <img class="img-dropdown" src="{{ asset('images/authentication-corner.png') }}" width="60" alt="" />
                    @foreach($items as $pItem)
                        @php
                            $pUrl = \App\Support\PluginNav::resolveUrl($pItem);
                            $pIcon = !empty($pItem['icon']) ? $pItem['icon'] : null;
                            $pBadge = \App\Support\PluginNav::resolveBadge($pItem);
                            $hasChildren = !empty($pItem['children']) && is_array($pItem['children']);
                        @endphp
                        @if($hasChildren)
                            <div class="dropdown-divider my-1"></div>
                            <h6 class="dropdown-header text-uppercase fs-11 text-700 py-1 px-3 mb-0">
                                @if($pIcon)<i class="{{ $pIcon }} me-1"></i>@endif
                                {{ __($pItem['title'] ?? '') }}
                            </h6>
                            @foreach($pItem['children'] as $child)
                                @php
                                    $cUrl = \App\Support\PluginNav::resolveUrl($child);
                                    $cIcon = !empty($child['icon']) ? $child['icon'] : null;
                                    $cBadge = \App\Support\PluginNav::resolveBadge($child);
                                @endphp
                                <a wire:navigate.hover wire:current="active" class="dropdown-item link-600 fw-medium d-flex align-items-center justify-content-between ps-4" href="{{ $cUrl }}">
                                    <span>
                                        @if($cIcon)<i class="{{ $cIcon }} me-1.5 fs-10"></i>@endif
                                        {{ __($child['title'] ?? '') }}
                                    </span>
                                    @if($cBadge)
                                        <span class="badge rounded-pill ms-2 {{ $cBadge['class'] }}">{{ $cBadge['text'] }}</span>
                                    @endif
                                </a>
                            @endforeach
                            <div class="dropdown-divider my-1"></div>
                        @else
                            <a wire:navigate.hover wire:current="active" class="dropdown-item link-600 fw-medium d-flex align-items-center justify-content-between" href="{{ $pUrl }}">
                                <span>
                                    @if($pIcon)<i class="{{ $pIcon }} me-1.5"></i>@endif
                                    {{ __($pItem['title'] ?? '') }}
                                </span>
                                @if($pBadge)
                                    <span class="badge rounded-pill ms-2 {{ $pBadge['class'] }}">{{ $pBadge['text'] }}</span>
                                @endif
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </li>
    @endforeach
@endif

