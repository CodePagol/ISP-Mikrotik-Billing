<?php

namespace App\Models;

use App\Http\Controllers\AvatarController;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasAvatar
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasRoles;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'address',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function defaultProfilePhotoUrl()
    {
        if ($this->profile_image) {
            return asset('storage/profile_images/'.$this->profile_image);
        }
        // If no profile image is set, generate a default avatar
        $avatarController = new AvatarController;

        return $avatarController->generateAvatar($this->name);
    }

    // Define the search scope
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', '%'.$search.'%')
                ->orWhere('email', 'like', '%'.$search.'%')
                ->orWhere('mobile', 'like', '%'.$search.'%')
                ->orWhere('address', 'like', '%'.$search.'%')
                ->orWhereHas('roles', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                });
            // ->orWhereHas('permissions', function ($q) use ($search) {
            //     $q->where('name', 'like', '%' . $search . '%');
            // });
        });
    }

    /**
     * Get the user's avatar URL for Filament.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->profile_photo_url;
    }

    /**
     * Get the reseller profile associated with this user.
     */
    public function reseller()
    {
        return $this->hasOne(Reseller::class);
    }
}

