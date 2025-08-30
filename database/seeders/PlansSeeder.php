<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;
use App\Models\Permission;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $freePermission = Permission::where('name', 'free')->first();
        $basePermission = Permission::where('name', 'base')->first();
        $fullPermission = Permission::where('name', 'full')->first();

        Plan::updateOrCreate([
            'name' => 'free',
        ], [
            'description' => 'Free plan with limited access',
            'permission_id' => $freePermission ? $freePermission->id : null,
        ]);
        Plan::updateOrCreate([
            'name' => 'base',
        ], [
            'description' => 'Base plan with standard access',
            'permission_id' => $basePermission ? $basePermission->id : null,
        ]);
        Plan::updateOrCreate([
            'name' => 'full',
        ], [
            'description' => 'Full plan with all features',
            'permission_id' => $fullPermission ? $fullPermission->id : null,
        ]);
    }
}
