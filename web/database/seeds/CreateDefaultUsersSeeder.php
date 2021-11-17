<?php

use Illuminate\Database\Seeder;

use App\Models\City;
use App\Models\User;
use App\Models\Partner;
use App\Models\Courier;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreateDefaultUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = Role::all();
        $permissions = Permission::all();
        $citiesIds = City::pluck('id')->toArray();

        $adminsData = [
            [
                'email'     => 'admin@zekra.com',
                'name'      => 'admin',
                'phone'     => '01114645469',
                'status'    => 1,
                'password'  => bcrypt('12345678')
            ]
        ];

        foreach ($adminsData as $adminData) {
            $admin = User::where('email', $adminData['email'])->first();
            if (empty($admin)) {
                $admin = user::create($adminData);
            }

            foreach ($roles as $role) {
                $admin->assignRole($role);
            }

            foreach ($permissions as $permission) {
                $admin->givePermissionTo($permission);
            }
        }


        $partnersData = [
            [
                "user"  => [
                    "name"      => "Zekera HQ Partner",
                    "email"     => "partner@zekra.com",
                    "phone"     => "01114644459",
                    "status"    => 1,
                    "password"  => bcrypt("12345678")
                ],
                "parnter"   => [
                    "fee"       => "10",
                    "status"    => "1",
                    "default"   => 1,
                    'city_id'   => array_random($citiesIds)
                ]
            ]
        ];

        foreach ($partnersData as $partnerData) {
            $partner = User::where('email', $partnerData['user']['email'])->first();
            if (empty($partner)) {
                $partner = user::create($partnerData['user']);
                $partner->partners()->create($partnerData['parnter']);
            }
            $partner->assignRole('partner');
            $partner->givePermissionTo('view-user');
        }

        $couriersData = [
            [
                "user"  => [
                    "name"      => "Zekera HQ Courier",
                    "email"     => "courier@zekra.com",
                    "phone"     => "01114644449",
                    "status"    => 1,
                    "password"  => bcrypt("12345678")
                ],
                "courier"   => [
                    "fee"                                   => "100",
                    "status"                                => "1",
                    "default"                               => 1,
                    'city_id'                               => array_random($citiesIds),
                    'cash_delivery_primary_amount'          => mt_rand(50, 100),
                    'cash_delivery_primary_amount_fee'      => mt_rand(10, 20),
                    'cash_delivery_additional_amount'       => mt_rand(50, 100),
                    'cash_delivery_additional_amount_fee'   => mt_rand(10, 20)
                ]
            ]
        ];

        foreach ($couriersData as $courierData) {
            $courier = User::where('email', $courierData['user']['email'])->first();
            if (empty($courier)) {
                $courier = user::create($courierData['user']);
                $courier->couriers()->create($courierData['courier']);
            }
            $courier->assignRole('courier');
            $courier->givePermissionTo('view-user');
        }
    }
}
