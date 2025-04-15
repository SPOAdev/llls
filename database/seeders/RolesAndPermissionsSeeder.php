<?php

namespace Database\Seeders;

use App\Models\User;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage_license',
            'manage_user',
            'query_license',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $user  = Role::firstOrCreate(['name' => 'user']);

        foreach ($permissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            if ($permission && !$admin->hasPermissionTo($permission)) {
                $admin->givePermissionTo($permission);
            }
        }

        $queryPermission = Permission::where('name', 'query_license')->first();
        if ($queryPermission && !$user->hasPermissionTo($queryPermission)) {
            $user->givePermissionTo($queryPermission);
        }

        $adminUser = User::find(1);
        if ($adminUser && !$adminUser->hasRole('admin')) {
            $adminUser->assignRole('admin');
        }
    }
}
