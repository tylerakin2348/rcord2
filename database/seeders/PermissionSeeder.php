<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $perm = Permission::firstOrCreate([
            'name' => 'manage_system_info',
        ], [
            'description' => 'Manage system information screens',
        ]);

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->permissions()->syncWithoutDetaching([$perm->id]);
        }
    }
}
