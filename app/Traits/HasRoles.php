<?php

namespace App\Traits;

use App\Exceptions\PermissionException;
use App\Models\Role;
use App\Models\Permission;
use Exception;

trait HasRoles
{
    public function assignRole($role): void
    {
        $stored = $this->getStoredRole($role);
        if (!$stored) {
            throw new PermissionException("Role " . $role . " not found");
        }
        $this->roles()->sync($stored->id);
    }

    public function hasRole(...$roles): bool
    {
        $roles = collect($roles)
            ->flatten()
            ->map(function ($role) {
                if (!$role) {
                    return false;
                }
                return $this->getStoredRole($role);
            })
            ->filter(function ($role) {
                return $role instanceof Role;
            })
            ->map->id
            ->all();

        if (count($roles) === 0)
            return false;
        $count = $this->roles()->whereIn('role_id', $roles)->count();

        return $count == count($roles);
    }

    public function hasAnyRole(...$roles): bool
    {
        $roles = collect($roles)
            ->flatten()
            ->map(function ($role) {
                if (!$role) {
                    return false;
                }
                return $this->getStoredRole($role);
            })
            ->filter(function ($role) {
                return $role instanceof Role;
            })
            ->map->id
            ->all();

        if (count($roles) === 0)
            return false;

        $count = $this->roles()->whereIn('role_id', $roles)->count();

        return $count > 0;
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'users_roles', 'user_id', 'role_id')
            ->withPivot('users_roles.active')
            ->where('roles.active', true)
            ->wherePivot('active', true);
    }

    protected function getStoredRole($role)
    {
        if (is_numeric($role)) {
            return Role::find($role);
        }

        if (is_string($role)) {
            return Role::where('name', $role)->first();
        }
        return $role;
    }

    public function hasPermissionTo($permission): bool
    {
        $count = Permission::join('roles_permissions', 'permissions.id', 'roles_permissions.permission_id')
            ->join('roles', 'roles.id', 'roles_permissions.role_id')
            ->join('users_roles', 'roles.id', 'users_roles.role_id')
            ->where('users_roles.active', true)
            ->where('users_roles.user_id', $this->id)
            ->where('permissions.name', $permission)
            ->where('roles.active', true)
            ->count();
        return $count > 0;
    }

    public function allRoles()
    {
        return $this->belongsToMany(Role::class, 'users_roles', 'user_id', 'role_id')->withPivot('active');
    }
}
