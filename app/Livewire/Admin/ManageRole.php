<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ManageRole extends Component
{
    use WithoutUrlPagination, WithPagination;

    public $roleType;

    public $name;

    public $search;

    public $permissionList = [];

    public $permissions = [];

    public $roleId;

    public $perPage = 10;

    public $confirmingRole = false;

    protected $listeners = ['roleEdit' => 'editRole', 'roleDelete' => 'deleteRole'];

    public function mount()
    {
        if (! hasAccess(['Super Admin'], ['create-user-role', 'edit-user-role', 'view-user-role', 'delete-user-role'])) {
            abort(403, 'Unauthorized Access.');
        }
    }

    public function newRole()
    {
        if (abortIfNoAccess(['Super Admin'], ['create-user-role'], 'You do not have permission to create roles.')) {
            return;
        }

        $this->reset(['roleType', 'roleId', 'name', 'permissions']);
        $this->permissionList = Permission::all();
        $this->roleType = 'Create New Role';
        $this->confirmingRole = true;
    }

    public function editRole($roleId)
    {
        if (abortIfNoAccess(['Super Admin'], ['edit-user-role'], 'You do not have permission to edit roles.')) {
            return;
        }

        $this->role = Role::find($roleId);
        if (! $this->role) {
            session()->flash('error', 'Role not found.');

            return;
        }

        if ($this->role->name === 'Super Admin') {
            session()->flash('error', 'Super Admin role cannot be edited.');

            return;
        }

        $this->roleType = 'Edit Role';
        $this->roleId = $roleId;
        $this->name = $this->role->name;
        $this->permissions = $this->role->permissions->pluck('id')->toArray();
        $this->permissionList = Permission::all();
        $this->confirmingRole = true;
    }

    public function saveRole()
    {
        if (abortIfNoAccess(['Super Admin'], ['edit-user-role'], 'You do not have permission to save roles.')) {
            return;
        }

        $this->validate([
            'name' => 'required|unique:roles,name,'.($this->roleId ?? 'NULL').'|max:255',
            'permissions' => 'array',
        ]);

        if ($this->roleId) {
            $role = Role::find($this->roleId);
            if (! $role) {
                session()->flash('error', 'Role not found.');

                return;
            }
            if ($role->name === 'Super Admin') {
                session()->flash('error', 'Super Admin role cannot be edited.');

                return;
            }
            $role->name = $this->name;
            $role->syncPermissions($this->permissions);
            $role->save();
            flash()->success('Role updated successfully.');
        } else {
            $role = Role::create(['guard_name' => 'web', 'name' => $this->name]);
            $role->syncPermissions($this->permissions);
            flash()->success('Role created successfully.');
        }

        $this->confirmingRole = false;
    }

    public function deleteRole($roleId, $roleName)
    {
        if (abortIfNoAccess(['Super Admin'], ['delete-role'], 'You do not have permission to delete roles.')) {
            return;
        }
        if ($roleName === 'Super Admin') {
            session()->flash('error', 'Super Admin role cannot be deleted.');

            return;
        }
        $this->roleId = $roleId;
        sweetalert()
            ->option('confirmButtonText', 'Yes')
            ->showDenyButton()
            ->warning(
                "Are you sure you want to delete this '{$roleName}' role?",
                ['title' => 'Confirm Deletion']
            );
    }

    #[On('sweetalert:confirmed')]
    public function onConfirmed(array $payload = []): void
    {
        try {
            if (! $this->roleId) {
                session()->flash('error', 'No role selected for deletion.');

                return;
            }

            $role = Role::find($this->roleId);

            if (! $role) {
                session()->flash('error', 'Role not found.');
                $this->roleId = null;

                return;
            }

            if ($role->name === 'Super Admin') {
                session()->flash('error', 'Super Admin role cannot be deleted.');
                $this->roleId = null;

                return;
            }

            $role->delete();
            $this->roleId = null;

            session()->flash('success', 'Role successfully deleted.');
        } catch (\Throwable $e) {
            dd($e->getMessage(), $e->getFile(), $e->getLine());
        }
    }

    #[On('sweetalert:denied')]
    public function onDeny(array $payload): void
    {
        session()->flash('info', 'Deletion cancelled.');
    }

    public function render()
    {
        $roles = Role::where('name', '!=', 'Reseller')->orderBy('id')->paginate($this->perPage);

        return view('livewire.admin.role.manage-role', ['roles' => $roles])->layout('layouts.app');
    }
}
