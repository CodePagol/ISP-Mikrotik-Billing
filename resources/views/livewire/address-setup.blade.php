<div class="px-md-4 zoom-in">
    <x-slot name="header">
        <h2 class="h4 font-weight-bold text-dark mb-0">
            <i class="bi bi-geo-alt me-2 text-success"></i>{{ __('Address Setup') }}
        </h2>
    </x-slot>

    <div class="row g-3">
        <!-- Edit / Create Form Panel -->
        <div class="col-lg-4 col-md-12">
            <x-mikrotik.section-form>
                <x-slot name="title">
                    <span class="text-success fw-bold">
                        <i class="bi bi-pencil-square me-2"></i>{{ $addressFieldId ? __('Edit Field') : __('Create Field') }}
                    </span>
                </x-slot>
                <x-slot name="aside">
                    @if(auth()->user()->can('address-setup-create') || $addressFieldId)
                        <form wire:submit.prevent="submit" x-data="{ input_type: @entangle('input_type') }">
                            <div class="mb-3">
                                <x-mikrotik.form-group
                                    column="col-12"
                                    label="{{ __('Label Name') }}"
                                    name="label"
                                    type="text"
                                    placeholder="{{ __('Enter label name (eg. Area, Road)') }}"
                                />
                            </div>

                            <div class="mb-3 bg-light p-3 rounded-3 border">
                                <div class="form-check mb-2">
                                    <input type="checkbox" id="required" class="form-check-input" wire:model="required">
                                    <label for="required" class="form-check-label fw-semibold text-dark">
                                        {{ __('Required Field') }} <span class="text-danger">*</span>
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" id="print_preview" class="form-check-input" wire:model="print_preview">
                                    <label for="print_preview" class="form-check-label text-muted small">
                                        {{ __('Preview in receipt') }}
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" id="complain_preview" class="form-check-input" wire:model="complain_preview">
                                    <label for="complain_preview" class="form-check-label text-muted small">
                                        {{ __('Preview in Complain List') }}
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3 bg-light p-3 rounded-3 border">
                                <x-mikrotik.form-group
                                    x-model="input_type"
                                    column="col-12 mb-2"
                                    label="{{ __('Field Input Type') }}"
                                    type="dropdownKey"
                                    name="input_type"
                                    placeholder="{{ __('Select Type') }}"
                                    :options="['dropdown' => __('Dropdown / List'), 'text' => __('Input Text (Single Line)'), 'textarea' => __('Input Box (Multi Line)')]"
                                />

                                <!-- Dropdown options manager -->
                                <div class="mt-3" x-show="input_type === 'dropdown'" x-cloak>
                                    <label for="dropdown_input" class="form-label fw-semibold text-dark mb-1" style="font-size: 0.85rem;">{{ __('List Options') }}</label>
                                    <div class="input-group input-group-sm mb-2 shadow-sm">
                                        <input type="text" wire:model="dropdown_input" id="dropdown_input" class="form-control" placeholder="{{ __('Add option item') }}">
                                        <button type="button" class="btn btn-success" wire:click="addTypeToList">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </div>
                                    <x-input-error for='dropdown_input' />

                                    <!-- Added options chips list -->
                                    @if (!empty($dropdown_list))
                                        <div class="d-flex flex-wrap gap-1 mt-2">
                                            @foreach ($dropdown_list as $index => $type)
                                                <span class="badge bg-success-subtle text-success border d-flex align-items-center gap-1 py-1 px-2 rounded-pill" style="font-size: 0.78rem;">
                                                    {{ $type }}
                                                    <i class="bi bi-x-circle-fill text-danger" style="cursor: pointer;" wire:click="removeTypeFromList({{ $index }})"></i>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                    <x-input-error for='dropdown_list' />
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                                @if($addressFieldId)
                                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" style="border-radius: 6px;" wire:click="$set('addressFieldId', null)">
                                        {{ __('Cancel') }}
                                    </button>
                                @endif
                                <button type="submit" class="btn btn-sm btn-success px-4" style="border-radius: 6px;">
                                    <i class="bi bi-save me-1"></i>{{ __('Save') }}
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-shield-lock fs-2 d-block mb-2"></i>
                            {{ __('You do not have permission to manage Address Setup.') }}
                        </div>
                    @endif
                </x-slot>
            </x-mikrotik.section-form>
        </div>

        <!-- Address Fields List & Sort Panel -->
        <div class="col-lg-5 col-md-12">
            <x-mikrotik.section-form>
                <x-slot name="title">
                    <span class="text-success fw-bold"><i class="bi bi-list-stars me-2"></i>{{ __('Address Fields & Preview') }}</span>
                </x-slot>
                <x-slot name="aside">
                    <div class="p-1">
                        @if (!empty($addressFields))
                            <ul wire:sortable="updateSortOrderAddress" id="sortable-list" class="list-group mb-3 gap-2">
                                @foreach ($addressFields as $field)
                                    <li wire:sortable.item="{{ $field['id'] }}" wire:key="field-{{ $field['id'] }}" class="list-group-item border rounded-3 p-3 shadow-sm" style="background: #fafafa;">
                                        <div class="row align-items-center g-2">
                                            <!-- Field Drag Handle & Label -->
                                            <div class="col-md-5 d-flex align-items-center gap-2">
                                                <i wire:sortable.handle class="bi bi-grid-3x2-gap text-muted" style="cursor: move;"></i>
                                                <span class="fw-bold text-dark">{{ $field['label'] }}</span>
                                                @if ($field['required'] == 1)
                                                    <span class="text-danger">*</span>
                                                @endif
                                            </div>
                                            <!-- Preview element -->
                                            <div class="col-md-4">
                                                @if ($field['input_type'] == 'dropdown')
                                                    <select class="form-select form-select-sm" disabled style="background-color: #fff;">
                                                        @foreach (json_decode($field['dropdown_list']) as $type)
                                                            <option value="{{ $type }}">{{ $type }}</option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    <input type="text" class="form-control form-control-sm" placeholder="{{ __($field['input_type']) }}" disabled style="background-color: #fff;">
                                                @endif
                                            </div>
                                            <!-- Action controls -->
                                            <div class="col-md-3 text-end d-flex justify-content-end gap-1">
                                                @can('address-setup-edit')
                                                    <button type="button" class="btn btn-xs btn-outline-success p-1" style="border-radius: 4px;" wire:click="edit({{ $field['id'] }})" title="{{ __('Edit') }}">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                @endcan

                                                @can('address-setup-delete')
                                                    <button type="button" class="btn btn-xs btn-outline-danger p-1" style="border-radius: 4px;" wire:click="delete({{ $field['id'] }})" title="{{ __('Delete') }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @endcan

                                                @if ($field['print_preview'] == 1)
                                                    <span class="badge bg-light text-success border d-inline-flex align-items-center p-1" title="{{ __('Prints in receipt') }}">
                                                        <i class="bi bi-printer"></i>
                                                    </span>
                                                @endif
                                                @if ($field['complain_preview'] == 1)
                                                    <span class="badge bg-light text-primary border d-inline-flex align-items-center p-1" title="{{ __('Shows in complain') }}">
                                                        <i class="bi bi-exclamation-triangle"></i>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                            <!-- Save Button -->
                            <button type="button" class="btn btn-sm btn-success px-4" style="border-radius: 6px;" wire:click="saveSortOrderAddress">
                                <i class="bi bi-save me-1"></i>{{ __('Save Fields Order') }}
                            </button>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-geo fs-2 d-block mb-2"></i>
                                {{ __('No address fields available. Create one to begin.') }}
                            </div>
                        @endif
                    </div>
                </x-slot>
            </x-mikrotik.section-form>
        </div>

        <!-- Receipt Layout Sorting Panel -->
        <div class="col-lg-3 col-md-12">
            <x-mikrotik.section-form>
                <x-slot name="title">
                    <span class="text-success fw-bold"><i class="bi bi-receipt me-2"></i>{{ __('Receipt Section') }}</span>
                </x-slot>
                <x-slot name="aside">
                    <div class="p-1">
                        <div class="text-muted small mb-2"><i class="bi bi-info-circle me-1"></i>{{ __('Drag fields to order how they print on receipt:') }}</div>
                        @if (!empty($receiptOrders))
                            <div wire:sortable="updateSortOrderReceipt" class="d-flex flex-column gap-2 mb-3">
                                @foreach ($receiptOrders as $field)
                                    <div wire:sortable.item="{{ $field['id'] }}" wire:key="field-{{ $field['id'] }}" class="d-flex align-items-center gap-2 border rounded-3 p-2 bg-light shadow-xs" style="cursor: move;">
                                        <i wire:sortable.handle class="bi bi-grid-3x2-gap text-muted"></i>
                                        <span class="small fw-semibold text-dark">{{ $field['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <!-- Save Button -->
                            <button type="button" class="btn btn-sm btn-success w-100" style="border-radius: 6px;" wire:click="saveSortOrderReceipt">
                                <i class="bi bi-save me-1"></i>{{ __('Save Receipt Order') }}
                            </button>
                        @else
                            <p class="text-muted text-center py-3 small">{{ __('No receipt address fields available.') }}</p>
                        @endif
                    </div>
                </x-slot>
            </x-mikrotik.section-form>
        </div>
    </div>
</div>
