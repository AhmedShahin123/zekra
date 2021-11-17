<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(LanguagesTableSeeder::class);
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(CounrtiesTableSeeder::class);
        $this->call(CreateDefaultUsersSeeder::class);
        $this->call(SettingsTableSeeder::class);
        $this->call(ShippingPricesSeeder::class);
        $this->call(CurrenciesTableSeeder::class);
        $this->call(PackagesSeeder::class);
        $this->call(ProductsTableSeeder::class);
    }
}
