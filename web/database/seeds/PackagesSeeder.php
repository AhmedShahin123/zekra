<?php

use App\Models\Package;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PackagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if(Package::count() == 0){
            for($i = 1; $i <= 5; $i++){
                $expireDate = Carbon::now()->addMonth($i);
                $packageData = [
                    'price'         => ($i * 10),
                    'credit_points' => $i,
                    'max_users'     => mt_rand(5, 20),
                    'expire_at'     => $expireDate
                ];
                Package::Create($packageData);
            }
        }
    }
}
