<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role as ModelRole;;
use App\Enums\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ModelRole::createOrUpdate(Role::SUPER_ADMIN_ROLE->value, 'super admin role', true, false, true);
        ModelRole::createOrUpdate(Role::OPERATION_ROLE->value, 'operation role', true, false, true);        
    }
}
