<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Enums\Role;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'name' => 'User',
                'last_name' => 'Admin',
                'email' => 'admin@proi.com',
                'password' => bcrypt('proi#2025'),
                'role' => Role::SUPER_ADMIN_ROLE->value,
            ],
            [
                'name' => 'User',
                'last_name' => 'Operator',
                'email' => 'operator@proi.com',
                'password' => bcrypt('proi#2025'),
                'role' => Role::OPERATION_ROLE->value,
            ],
        ];
        
        foreach ($users as $user) {
            $usr = User::where('email', $user['email'])->first();
            if (!$usr) {
                $role = $user['role'];
                unset($user['role']);
                $usr = User::create($user);
                $usr->assignRole($role);
            }
        }
    }
}
