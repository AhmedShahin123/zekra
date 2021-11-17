<?php

use App\Models\Currency;
use Illuminate\Database\Seeder;

use App\Traits\Helper;

class CurrenciesTableSeeder extends Seeder
{
    use Helper;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if(Currency::count() == 0){
            try{
                $this->updateCurrencies();
            }catch(\Exception $error){
                print($error->getMessage());
            }
            
        }
    }
}
