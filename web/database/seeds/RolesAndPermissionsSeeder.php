<?php

use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            'super-admin',
            'partner',
            'courier'
        ];
        $permissions = [
            'create-user',
            'update-user',
            'delete-user',
            'view-user',
            'create-role',
            'update-role',
            'delete-role',
            'view-role',
            'view-user',
            'create-permission',
            'update-permission',
            'delete-permission',
            'view-permission',
            'view-order',
            'view-transaction',
            'create-transaction',
            'update-transaction',
            'delete-transaction',
            'view-report',
            'view-album',
            'view-image',
            'view-partner',
            'view-city',
            'view-product',
            'view-country',
            'view-courier',
            'view-partnerOrder',
            'view-courierOrder',
            'view-language',
            'create-language',
            'update-language',
            'delete-language',
            'view-coupon',
            'create-coupon',
            'update-coupon',
            'delete-coupon',
            'view-receipt',
            'view-email',
            'view-payment',
            'create-payment',
            'update-payment',
            'delete-payment',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }
}
