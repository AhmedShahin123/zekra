<?php

use Illuminate\Database\Seeder;

use App\Models\Language;

class LanguagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $languages = [
            [
                'locale'        => 'en',
                'name'          => 'English',
                'native_name'   => 'English'
            ],
            [
                'locale'        => 'ar',
                'name'          => 'Arabic',
                'native_name'   => 'العربية'
            ]
        ];

        foreach($languages as $language){
            Language::firstOrCreate($language);
        }
    }
}
