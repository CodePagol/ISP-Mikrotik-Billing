<div>

    @if ($showModal)
        <div class="modal fade show"
            style="display: block; background: rgba(9, 13, 22, 0.85); backdrop-filter: blur(16px); z-index: 1055;"
            tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content text-white"
                    style="background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 16px; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" style="font-family: var(--font-heading);">
                            <i class="bi bi-cart-fill text-success me-2"></i>Apply for <span
                                class="text-gradient">{{ $packageName }}</span>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" aria-label="Close"
                            wire:click="closeModal"></button>
                    </div>
                    <form wire:submit.prevent="submitRequest">
                        <div class="modal-body">
                            <!-- Package Details Summary -->
                            <div class="p-3 mb-3 rounded-3"
                                style="border: 1px solid var(--glass-border);">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="d-block text-uppercase fs-11">Selected Package</small>
                                        <strong class="fs-6">{{ $packageName }}</strong>
                                    </div>
                                    <div class="text-end">
                                        <small class="d-block text-uppercase fs-11">Price</small>
                                        <strong class="fs-6 text-success">{{ number_format($price, 0) }} ৳ /
                                            Month</strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Customer Details -->
                            <div class="mb-3">
                                <label class="form-label small">Your Name <span
                                        class="text-danger">*</span></label>
                                <input wire:model="name" type="text" class="form-control"
                                    placeholder="Enter your full name" required
                                    style="border: 1px solid var(--glass-border) !important; color: #fff;">
                                @error('name')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Mobile Number <span
                                        class="text-danger">*</span></label>
                                <div wire:ignore>
                                    <input wire:model="phone" type="text" class="form-control"
                                        placeholder="Enter your contact number" required
                                        x-data="intlTelInput('phone')"
                                        style="border: 1px solid var(--glass-border) !important; color: #fff;">
                                </div>
                                @error('phone')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Email Address (Optional)</label>
                                <input wire:model="email" type="email" class="form-control"
                                    placeholder="Enter your email address"
                                    style="border: 1px solid var(--glass-border) !important; color: #fff;">
                                @error('email')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Installation Address <span
                                        class="text-danger">*</span></label>
                                <textarea wire:model="address" class="form-control" rows="3"
                                    placeholder="Enter your complete installation address" required
                                    style="border: 1px solid var(--glass-border) !important; color: #fff;"></textarea>
                                @error('address')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-0">
                                <label class="form-label small">Additional Notes (Optional)</label>
                                <textarea wire:model="notes" class="form-control" rows="2" placeholder="Any special requests or instructions..."
                                    style="border: 1px solid var(--glass-border) !important; color: #fff;"></textarea>
                                @error('notes')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-secondary rounded-pill px-4"
                                wire:click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-success rounded-pill px-4"
                                style="background: var(--primary-gradient); border: none;">Submit Application</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" style="background: rgba(9, 13, 22, 0.85); z-index: 1040;"></div>
    @endif
</div>
