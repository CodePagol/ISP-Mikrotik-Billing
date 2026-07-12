<div class="container-fluid p-0 h-100 d-flex flex-column">
    @if (session()->has('message'))
        <div class="alert alert-success d-flex align-items-center rounded-3 border-0 shadow-lg mb-4 text-white" 
             style="background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.8), rgba(var(--secondary-rgb, 255, 114, 0), 0.8)); backdrop-filter: blur(8px);" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div>
                {{ session('message') }}
            </div>
        </div>
    @endif

    <form wire:submit.prevent="submitComment" class="h-100 d-flex flex-column justify-content-between">
        <div class="mb-2 row align-items-center">
            <label for="name" class="form-label col-form-label col-4 col-sm-3 mb-0"><i class="bi bi-person-fill me-1 text-primary"></i> {{ __('Name') }}<span class="text-danger">*</span></label>
            <div class="col-8 col-sm-9">
                <input type="text" class="form-control mb-0" wire:model="name" id="name" placeholder="{{ __('Name') }}">
                <x-input-error for='name' />
            </div>
        </div>

        <div class="mb-2 row align-items-center">
            <label for="mobile" class="form-label col-form-label col-4 col-sm-3 mb-0"><i class="bi bi-telephone-fill me-1 text-primary"></i> {{ __('Mobile') }}<span class="text-danger">*</span></label>
            <div class="col-8 col-sm-9">
                <div wire:ignore>
                    <input type="text" class="form-control mb-0" wire:model="mobile" id="mobile" x-data="intlTelInput('mobile')" placeholder="{{ __('Mobile number') }}">
                </div>
                <x-input-error for='mobile' />
            </div>
        </div>

        <div class="mb-2 row align-items-center">
            <label for="email" class="form-label col-form-label col-4 col-sm-3 mb-0"><i class="bi bi-envelope-fill me-1 text-primary"></i> {{ __('Email') }}<span class="text-danger">*</span></label>
            <div class="col-8 col-sm-9">
                <input type="email" class="form-control mb-0" wire:model="email" id="email" placeholder="name@example.com">
                <x-input-error for='email' />
            </div>
        </div>

        <div class="mb-2 row align-items-start">
            <label for="comment" class="form-label col-form-label col-4 col-sm-3 mb-0 pt-1"><i class="bi bi-chat-text-fill me-1 text-primary"></i> {{ __('Message') }}<span class="text-danger">*</span></label>
            <div class="col-8 col-sm-9">
                <textarea class="form-control mb-0" wire:model="comment" id="comment" rows="3" placeholder="{{ __('Write your message here...') }}"></textarea>
                <x-input-error for='comment' />
            </div>
        </div>

        <div class="mb-3 row align-items-center">
            <label for="captcha" class="form-label col-form-label col-4 col-sm-3 mb-0"><i class="bi bi-shield-lock-fill me-1 text-primary"></i> {{ __('Captcha') }}<span class="text-danger">*</span></label>
            <div class="col-8 col-sm-9">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-success fw-bold flex-shrink-0">{{ $num1 }} + {{ $num2 }} =</span>
                    <input type="text" class="form-control mb-0" wire:model="captcha" id="captcha" placeholder="{{ __('Answer') }}">
                </div>
                <x-input-error for='captcha' />
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mt-2">
            <span wire:loading.remove wire:target="submitComment"><i class="bi bi-send-fill me-2"></i>{{ __('Send Message') }}</span>
            <span wire:loading wire:target="submitComment" class="spinner-border spinner-border-sm me-2" role="status"></span>
            <span wire:loading wire:target="submitComment">{{ __('Sending...') }}</span>
        </button>
    </form>
</div>
