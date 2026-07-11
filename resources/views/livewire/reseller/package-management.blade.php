<div class="row">
    <!-- Packages List Column -->
    <div class="col-lg-8 mb-4">
        <!-- Assigned Packages -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-box2-fill text-primary me-2"></i>{{ __('Assigned Packages') }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">{{ __('Package Name') }}</th>
                                <th>{{ __('Price') }}</th>
                                <th>{{ __('Speed') }}</th>
                                <th>{{ __('Router') }}</th>
                                <th>{{ __('Description') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignedPackages as $pkg)
                                <tr>
                                    <td class="fw-bold text-dark ps-3">{{ $pkg->package }}</td>
                                    <td class="fw-semibold text-success">৳{{ number_format($pkg->price, 2) }}</td>
                                    <td>{{ $pkg->speed ?? 'N/A' }}</td>
                                    <td><span class="badge bg-secondary-subtle text-secondary">{{ $pkg->router_name ?? __('Global') }}</span></td>
                                    <td class="text-muted small">{{ $pkg->description ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">{{ __('No packages assigned by admin yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Custom Packages -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-box-seam text-success me-2"></i>{{ __('My Custom Packages') }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">{{ __('Package Name') }}</th>
                                <th>{{ __('Price') }}</th>
                                <th>{{ __('Speed') }}</th>
                                <th>{{ __('Plan Label') }}</th>
                                <th>{{ __('Featured') }}</th>
                                <th class="text-end pe-3">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customPackages as $pkg)
                                <tr>
                                    <td class="fw-bold text-dark ps-3">{{ $pkg->package }}</td>
                                    <td class="fw-semibold text-success">৳{{ number_format($pkg->price, 2) }}</td>
                                    <td>{{ $pkg->speed ?? 'N/A' }}</td>
                                    <td>{{ $pkg->plan_label ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge {{ $pkg->is_featured ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $pkg->is_featured ? __('Yes') : __('No') }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="d-flex justify-content-end gap-1">
                                            <button type="button" wire:click="edit({{ $pkg->id }})" class="btn btn-sm btn-outline-primary" title="{{ __('Edit Package') }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button type="button" wire:click="delete({{ $pkg->id }})" wire:confirm="{{ __('Are you sure you want to delete this package?') }}" class="btn btn-sm btn-outline-danger" title="{{ __('Delete Package') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">{{ __('No custom packages created yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Column -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi {{ $isEditing ? 'bi-pencil-fill text-warning' : 'bi-plus-circle-fill text-primary' }} me-2"></i>
                    {{ $isEditing ? __('Edit Custom Package') : __('Create Custom Package') }}
                </h5>
            </div>
            <div class="card-body">
                <form wire:submit.prevent="save">
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('Package Name (Unique)') }}</label>
                        <input type="text" wire:model="package" class="form-control" required placeholder="{{ __('e.g. Reseller_10Mbps') }}">
                        @error('package') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('Price (BDT)') }}</label>
                        <input type="number" step="0.01" wire:model="price" class="form-control" required placeholder="0.00">
                        @error('price') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('Speed (e.g. 10 Mbps)') }}</label>
                        <input type="text" wire:model="speed" class="form-control" required placeholder="{{ __('e.g. 10 Mbps') }}">
                        @error('speed') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('Description (Optional)') }}</label>
                        <textarea wire:model="description" class="form-control" rows="2" placeholder="{{ __('Brief description') }}"></textarea>
                        @error('description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" wire:model="is_featured" id="isFeatured">
                        <label class="form-check-label fw-semibold" for="isFeatured">{{ __('Featured Package') }}</label>
                        @error('is_featured') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        @if($isEditing)
                            <button type="submit" class="btn btn-warning flex-fill rounded-3 text-white fw-bold">{{ __('Update Package') }}</button>
                            <button type="button" wire:click="cancelEdit" class="btn btn-secondary rounded-3 fw-bold">{{ __('Cancel') }}</button>
                        @else
                            <button type="submit" class="btn btn-primary flex-fill rounded-3 fw-bold">{{ __('Create Package') }}</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
