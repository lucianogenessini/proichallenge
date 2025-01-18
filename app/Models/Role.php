<?php

namespace App\Models;

use App\Helpers\ClassHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'description',
        'readonly',
        'show_in_front',
        'active',
    ];

    public static $rules = [
        'name' => 'required|max:30|unique:roles,name',
        'description' => 'required|max:70',
        'permissions' => 'required|array',
        'permissions.*' => 'required|exists:permissions,id',
        'readonly' => 'required|boolean',
        'show_in_front' => 'required|boolean',
        'active' => 'required|boolean',
    ];

    protected $casts = [
        'readonly' => 'boolean',
        'show_in_front' => 'boolean',
        'active' => 'boolean',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'roles_permissions', 'role_id', 'permission_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'users_roles', 'role_id', 'user_id');
    }

    public function assignPermission(...$permissions): void
    {
        $permissions = collect($permissions)
            ->flatten()
            ->map(function ($permission) {
                if (!$permission) {
                    return false;
                }
                if ($permission instanceof Permission) {
                    return $permission;
                }
                return Permission::where('name', $permission)->first();
            })
            ->filter(function ($permission) {
                return $permission instanceof Permission;
            })
            ->map->id
            ->all();

        $count = $this->permissions()->sync($permissions, false);
    }

    public static function createOrUpdate(
        string      $name,
        string|null $description,
        bool        $readonly,
        bool        $show_in_front,
        bool        $active,
    )
    {
        $role = Role::where('name', $name)->first();
        if (!$role) {
            $description = $description ?? ClassHelper::i18nRevert($name);
            $role = self::create([
                'name' => $name,
                'description' => $description,
                'readonly' => $readonly,
                'active' => $active,
                'show_in_front' => $show_in_front,
            ]);
        } else {
            $role->description = $description;
            $role->readonly = $readonly;
            $role->active = $active;
            $role->show_in_front = $show_in_front;
            $role->save();
        }
        return $role;
    }
}
