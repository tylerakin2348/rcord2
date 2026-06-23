<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'view_recordings', 'description' => 'View recordings'],
            ['name' => 'edit_recordings', 'description' => 'Edit recordings'],
            ['name' => 'delete_recordings', 'description' => 'Delete recordings'],
            ['name' => 'manage_users', 'description' => 'Manage users'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm['name']], $perm);
        }

            // Grant view, edit, and delete recording permissions to all users
            $defaultPermissions = Permission::whereIn('name', [
                'view_recordings',
                'edit_recordings',
                'delete_recordings',
            ])->get();

            foreach (\App\Models\User::all() as $user) {
                foreach ($defaultPermissions as $permission) {
                    $user->givePermissionTo($permission);
                }
            }
            // Grant 'manage_users' permission to user with id 2
            $manageUsersPermission = Permission::where('name', 'manage_users')->first();
            $user2 = \App\Models\User::find(2);
            if ($user2 && $manageUsersPermission) {
                $user2->givePermissionTo($manageUsersPermission);
            }
    }
}
