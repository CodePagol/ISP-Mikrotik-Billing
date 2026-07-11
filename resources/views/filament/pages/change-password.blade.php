<x-filament-panels::page>

    <div class="cp-max-w-lg cp-mx-auto cp-space-y-6">

        {{-- Header --}}
        <div class="cp-relative cp-overflow-hidden cp-rounded-3xl cp-bg-gradient-to-br cp-from-rose-500 cp-to-pink-600 cp-p-6 cp-text-white cp-shadow-2xl cp-shadow-rose-900/30">
            <div class="cp-absolute cp-right-0 cp-top-0 cp-opacity-10 cp-pointer-events-none">
                <svg viewBox="0 0 200 200" width="200" height="200"><circle cx="170" cy="30" r="100" fill="white"/></svg>
            </div>
            <div class="cp-relative cp-flex cp-items-center cp-gap-4">
                <div class="cp-p-3 cp-bg-white/20 cp-rounded-2xl">
                    <svg class="cp-w-8 cp-h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="cp-text-2xl cp-font-black">Change Password</h2>
                    <p class="cp-text-rose-100 cp-text-sm cp-mt-0.5">Update your PPPoE account password</p>
                </div>
            </div>
        </div>

        {{-- Success Alert --}}
        @if($success)
            <div class="cp-p-4 cp-bg-emerald-500/10 cp-border cp-border-emerald-500/20 cp-text-emerald-600 dark:cp-text-emerald-400 cp-rounded-2xl cp-text-sm cp-flex cp-items-center cp-gap-3">
                <span class="cp-p-2 cp-bg-emerald-500/20 cp-rounded-xl cp-text-emerald-500 cp-flex cp-items-center cp-justify-center cp-shrink-0">
                    <svg class="cp-w-5 cp-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <div>
                    <p class="cp-font-bold">Password changed successfully!</p>
                    <p class="cp-text-xs cp-mt-0.5 cp-opacity-75">Your new password is now active. Please remember it for your next login.</p>
                </div>
            </div>
        @endif

        {{-- Password Form --}}
        <div class="cp-bg-white dark:cp-bg-slate-900/60 cp-border cp-border-gray-100 dark:cp-border-white/5 cp-rounded-3xl cp-shadow-xl cp-p-6">
            <form wire:submit.prevent="save" class="cp-space-y-5">

                {{-- Current Password --}}
                <div>
                    <label for="cp_current" class="cp-text-xs cp-font-bold cp-text-gray-500 dark:cp-text-slate-400 cp-uppercase cp-tracking-wider cp-block cp-mb-2">Current Password</label>
                    <div class="cp-relative">
                        <div class="cp-absolute cp-inset-y-0 cp-left-0 cp-pl-4 cp-flex cp-items-center cp-pointer-events-none">
                            <svg class="cp-w-4 cp-h-4 cp-text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input id="cp_current" type="password" wire:model="currentPassword" autocomplete="current-password"
                            placeholder="Enter your current password"
                            class="cp-w-full cp-pl-10 cp-pr-4 cp-py-3.5 cp-bg-gray-50 dark:cp-bg-slate-950 cp-border cp-border-gray-200 dark:cp-border-white/10 cp-rounded-2xl cp-text-sm cp-text-gray-900 dark:cp-text-white cp-placeholder-gray-400 focus:cp-outline-none focus:cp-ring-2 focus:cp-ring-rose-500/20 focus:cp-border-rose-500 cp-transition-all @error('currentPassword') cp-border-rose-500 focus:cp-ring-rose-500/20 focus:cp-border-rose-500 @enderror">
                    </div>
                    @error('currentPassword') <p class="cp-text-xs cp-text-rose-500 cp-mt-1.5 cp-flex cp-items-center cp-gap-1">
                        <svg class="cp-w-3.5 cp-h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        {{ $message }}
                    </p> @enderror
                </div>

                <div class="cp-border-t cp-border-gray-100 dark:cp-border-white/5 cp-pt-5">
                    {{-- New Password --}}
                    <div class="cp-mb-4">
                        <label for="cp_new" class="cp-text-xs cp-font-bold cp-text-gray-500 dark:cp-text-slate-400 cp-uppercase cp-tracking-wider cp-block cp-mb-2">New Password</label>
                        <div class="cp-relative">
                            <div class="cp-absolute cp-inset-y-0 cp-left-0 cp-pl-4 cp-flex cp-items-center cp-pointer-events-none">
                                <svg class="cp-w-4 cp-h-4 cp-text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                            </div>
                            <input id="cp_new" type="password" wire:model="newPassword" autocomplete="new-password"
                                placeholder="Min. 6 characters"
                                class="cp-w-full cp-pl-10 cp-pr-4 cp-py-3.5 cp-bg-gray-50 dark:cp-bg-slate-950 cp-border cp-border-gray-200 dark:cp-border-white/10 cp-rounded-2xl cp-text-sm cp-text-gray-900 dark:cp-text-white cp-placeholder-gray-400 focus:cp-outline-none focus:cp-ring-2 focus:cp-ring-rose-500/20 focus:cp-border-rose-500 cp-transition-all @error('newPassword') cp-border-rose-500 @enderror">
                        </div>
                        @error('newPassword') <p class="cp-text-xs cp-text-rose-500 cp-mt-1.5 cp-flex cp-items-center cp-gap-1">
                            <svg class="cp-w-3.5 cp-h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            {{ $message }}
                        </p> @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <label for="cp_confirm" class="cp-text-xs cp-font-bold cp-text-gray-500 dark:cp-text-slate-400 cp-uppercase cp-tracking-wider cp-block cp-mb-2">Confirm New Password</label>
                        <div class="cp-relative">
                            <div class="cp-absolute cp-inset-y-0 cp-left-0 cp-pl-4 cp-flex cp-items-center cp-pointer-events-none">
                                <svg class="cp-w-4 cp-h-4 cp-text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <input id="cp_confirm" type="password" wire:model="confirmPassword" autocomplete="new-password"
                                placeholder="Re-enter your new password"
                                class="cp-w-full cp-pl-10 cp-pr-4 cp-py-3.5 cp-bg-gray-50 dark:cp-bg-slate-950 cp-border cp-border-gray-200 dark:cp-border-white/10 cp-rounded-2xl cp-text-sm cp-text-gray-900 dark:cp-text-white cp-placeholder-gray-400 focus:cp-outline-none focus:cp-ring-2 focus:cp-ring-rose-500/20 focus:cp-border-rose-500 cp-transition-all @error('confirmPassword') cp-border-rose-500 @enderror">
                        </div>
                        @error('confirmPassword') <p class="cp-text-xs cp-text-rose-500 cp-mt-1.5 cp-flex cp-items-center cp-gap-1">
                            <svg class="cp-w-3.5 cp-h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            {{ $message }}
                        </p> @enderror
                    </div>
                </div>

                {{-- Password Requirements --}}
                <div class="cp-p-4 cp-bg-blue-50 dark:cp-bg-blue-500/10 cp-rounded-2xl">
                    <p class="cp-text-xs cp-font-bold cp-text-blue-600 dark:cp-text-blue-400 cp-mb-2">Password Requirements</p>
                    <ul class="cp-space-y-1">
                        @foreach(['At least 6 characters long', 'Different from your current password', 'Remember it for future logins'] as $req)
                            <li class="cp-flex cp-items-center cp-gap-2 cp-text-xs cp-text-blue-500 dark:cp-text-blue-400/80">
                                <svg class="cp-w-3 cp-h-3 cp-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/>
                                </svg>
                                {{ $req }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Submit --}}
                <button type="submit" wire:loading.attr="disabled"
                    class="cp-w-full cp-py-4 cp-px-6 cp-bg-gradient-to-r cp-from-rose-500 cp-to-pink-600 hover:cp-from-rose-400 hover:cp-to-pink-500 cp-text-white cp-font-bold cp-rounded-2xl cp-shadow-lg cp-shadow-rose-500/20 cp-transition-all cp-duration-200 hover:cp--translate-y-0.5 active:cp-translate-y-0 cp-flex cp-items-center cp-justify-center cp-gap-2">
                    <span wire:loading.remove class="cp-flex cp-items-center cp-gap-2">
                        <svg class="cp-w-4 cp-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        Update Password
                    </span>
                    <span wire:loading class="cp-inline-block cp-w-4 cp-h-4 cp-border-2 cp-border-white/30 cp-border-t-white cp-rounded-full cp-animate-spin"></span>
                    <span wire:loading>Saving...</span>
                </button>

            </form>
        </div>

        {{-- Warning Note --}}
        <div class="cp-p-4 cp-bg-amber-50 dark:cp-bg-amber-500/10 cp-border cp-border-amber-100 dark:cp-border-amber-500/20 cp-rounded-2xl cp-text-xs cp-text-amber-600 dark:cp-text-amber-400 cp-flex cp-items-start cp-gap-2">
            <svg class="cp-w-4 cp-h-4 cp-shrink-0 cp-mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span>Changing your password here updates your <strong>portal login</strong>. If your ISP also uses this password for your router/WiFi, you may need to update your devices manually.</span>
        </div>

    </div>
</x-filament-panels::page>
