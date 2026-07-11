<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Rules\ValidPhoneDigits;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class ManageUser extends Component
{
    use WithoutUrlPagination, WithPagination;

    public $user;

    public $userType;

    public $name;

    public $email;

    public $address;

    public $password;

    public $password_confirmation;

    public $userId;

    public $userRoles;

    public $mobile = '880';

    public $roles = [];

    public $search = '';

    public $perPage = 10;

    public function updatedRoles($value)
    {
        if (in_array('Super Admin', $this->roles)) {
            $this->roles = ['Super Admin'];
        }
    }

    /**
     * Indicates if the model is being confirmed.
     *
     * @var bool
     */
    public $confirmingUser = false;

    protected $listeners = ['userEdit' => 'editUser', 'userDelete' => 'deleteUser'];

    protected $messages = [
        'mobile.regex' => 'Mobile number must start with "880" and be 11 digits long',
    ];

    public function mount()
    {
        if (! hasAccess(['Super Admin'], ['create-user', 'edit-user', 'view-user'])) {
            abort(403, 'Unauthorized action.');
        }
    }

    public function userRoles()
    {
        $query = Role::where('name', '!=', 'Reseller');

        if (! auth()->user()->hasRole('Super Admin')) {
            $query->where('name', '!=', 'Super Admin');
        }

        $this->userRoles = $query->pluck('name')->all();
    }

    public function newUser()
    {
        if (abortIfNoAccess(['Super Admin'], ['create-user'], 'You do not have permission to create users.')) {
            return;
        }
        $this->userRoles();
        $this->userType = 'Create New User';
        $this->confirmingUser = true;
    }

    public function editUser($userId)
    {
        if (abortIfNoAccess(['Super Admin'], ['edit-user'], 'You do not have permission to edit users.')) {
            return;
        }

        $targetUser = User::find($userId);
        if ($targetUser && $targetUser->hasRole('Super Admin') && !auth()->user()->hasRole('Super Admin')) {
            flash()->error('Only Super Admins can edit other Super Admins.');

            return;
        }

        $this->userRoles();

        $this->userType = 'Edit User';
        $this->userId = $userId;
        $this->user = $targetUser->makeHidden('password');
        if (! $this->user) {
            flash()->error('User not found.');

            return;
        }
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->mobile = $this->user->mobile ?? '880';
        $this->address = $this->user->address;
        $this->roles = $this->user->getRoleNames()->toArray();
        $this->confirmingUser = true;
    }

    public function deleteUser($userId, $UserName, $userEmail): void
    {
        if (abortIfNoAccess(['Super Admin'], ['delete-user'], 'You do not have permission to delete users.')) {
            return;
        }

        $targetUser = User::find($userId);
        if ($targetUser && $targetUser->hasRole('Super Admin') && !auth()->user()->hasRole('Super Admin')) {
            flash()->error('Only Super Admins can delete other Super Admins.');

            return;
        }

        $this->userId = $userId;

        sweetalert()
            ->option('confirmButtonText', 'Yes')
            ->showDenyButton()
            ->warning(
                "Are you sure you want to delete {$UserName} ({$userEmail})?",
                ['title' => 'Confirm Deletion']
            );
    }

    #[On('sweetalert:confirmed')]
    public function onConfirmed(array $payload): void
    {
        // Delete the user here
        $targetUser = User::find($this->userId);
        if ($targetUser && $targetUser->hasRole('Super Admin') && !auth()->user()->hasRole('Super Admin')) {
            flash()->error('Only Super Admins can delete other Super Admins.');

            return;
        }

        if ($targetUser) {
            $targetUser->delete();
        }

        flash()->success('User successfully deleted.');
    }

    #[On('sweetalert:denied')]
    public function onDeny(array $payload): void
    {
        flash()->info('Deletion cancelled.');
    }

    public function submitUser()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email:rfc,dns|max:255|unique:users,email,'.$this->userId,
            'mobile' => ['nullable', 'string', new ValidPhoneDigits],
            'address' => 'nullable|string|max:255',
            'roles' => 'required|exists:roles,name',
        ];

        // Conditionally apply password rules
        if (! $this->userId) {
            $rules['password'] = 'required|string|min:8|confirmed';
            $rules['password_confirmation'] = 'required|string|min:8';
        } else {
            $rules['password'] = 'nullable|string|min:8|confirmed';
            $rules['password_confirmation'] = 'nullable|string|min:8';
        }

        $this->validate($rules);

        if (in_array('Super Admin', $this->roles) && !auth()->user()->hasRole('Super Admin')) {
            flash()->error('Only Super Admins can assign the Super Admin role.');

            return;
        }

        if ($this->userId) {
            $existingUser = User::find($this->userId);
            if ($existingUser && $existingUser->hasRole('Super Admin') && !auth()->user()->hasRole('Super Admin')) {
                flash()->error('Only Super Admins can edit other Super Admins.');

                return;
            }
        }

        User::updateOrCreate(
            ['id' => $this->userId],
            [
                'name' => $this->name,
                'email' => $this->email,
                'mobile' => $this->mobile ?? null,
                'address' => $this->address ?? null,
                // 'password' => $this->password ? bcrypt($this->password) : null,
                'password' => $this->userId && ! $this->password ? $this->user->password : bcrypt($this->password),
            ]
        )->syncRoles($this->roles);

        $this->reset([
            'name',
            'email',
            'mobile',
            'address',
            'password',
            'password_confirmation',
            'userId',
        ]);
        $this->user = null;
        $this->userType = null;
        $this->roles = [];

        if ($this->userId) {
            flash()->success('User has been updated successfully.');
        } else {
            flash()->success('User has been created successfully.');
        }

        $this->confirmingUser = false;
    }

    /**
     * Render the component.
     *
     * @return View
     */
    public function render()
    {
        $users = User::search($this->search)
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'Reseller');
            })
            ->paginate($this->perPage);

        return view('livewire.admin.user.manage-user', ['users' => $users])->layout('layouts.app');
    }
}
