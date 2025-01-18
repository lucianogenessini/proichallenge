<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\ClassHelper;

class Permission extends Model
{
    use HasFactory;
    
    public static function createOrUpdate(
        string $name,
        string|null $description,
        string $group,
        array|null $roles = []
    )
    {   
        $permission = Permission::where('name', $name)->first();
        if (! $permission) {
            $description = $description ?? ClassHelper::i18nRevert($name);
            $permission = self::create([
                'name' => $name,
                'description' => $description,
                'group' => $group,
            ]);
        }
        
        foreach ($roles as $role) {
            $role = Role::where('name', $role)->first();
            $role?->assignPermission($permission);
        }

        return $permission;
    }
    
}
