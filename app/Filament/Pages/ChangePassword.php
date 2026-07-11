<?php

namespace App\Filament\Pages;

use App\Models\MainSiteData;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class ChangePassword extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-lock-closed';

    protected string $view = 'filament.pages.change-password';

    protected static ?string $navigationLabel = 'Change Password';

    protected static ?string $title = 'Change Password';

    protected static ?int $navigationSort = 7;

    public string $currentPassword = '';

    public string $newPassword = '';

    public string $confirmPassword = '';

    public bool $success = false;

    public static function shouldRegisterNavigation(): bool
    {
        $changePasswordEnabled = MainSiteData::getValue('portal_change_password_enabled', 1);

        return $changePasswordEnabled && auth()->guard('ppp')->check();
    }

    public function mount(): void
    {
        $changePasswordEnabled = MainSiteData::getValue('portal_change_password_enabled', 1);
        if (! $changePasswordEnabled || ! Auth::guard('ppp')->check()) {
            abort(403);
        }
    }

    public function save(): void
    {
        $this->success = false;

        $this->validate([
            'currentPassword' => 'required|string',
            'newPassword' => 'required|string|min:6|max:64',
            'confirmPassword' => 'required|same:newPassword',
        ], [
            'confirmPassword.same' => 'The confirmation password does not match.',
            'newPassword.min' => 'New password must be at least 6 characters.',
        ]);

        $user = Auth::guard('ppp')->user();

        // PPPoE passwords are stored as plain text (synced from MikroTik)
        if ($user->password !== $this->currentPassword) {
            $this->addError('currentPassword', 'The current password is incorrect.');

            return;
        }

        if ($this->currentPassword === $this->newPassword) {
            $this->addError('newPassword', 'New password must be different from the current password.');

            return;
        }

        try {
            app(\App\Http\Controllers\MikrotikController::class)->updatePPPSecret($user->router_name, $user->username, 'password', $this->newPassword);
        } catch (\Exception $e) {
            \Log::error('ChangePassword router sync failed: '.$e->getMessage());
            $this->addError('newPassword', 'Failed to update password on the router. Please try again.');

            return;
        }

        $user->update(['password' => $this->newPassword]);

        $this->currentPassword = '';
        $this->newPassword = '';
        $this->confirmPassword = '';
        $this->success = true;
    }
}
