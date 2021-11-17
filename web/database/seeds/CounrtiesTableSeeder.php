<?php

use Illuminate\Database\Seeder;

use App\Models\Country;
use App\Models\CountryTranslation;
use App\Models\City;
use App\Models\CityTranslation;

class CounrtiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $countries = [
            [
                'status'	=> '1',
                'code'		=> '20',
                'currency'  => 'EGP',
                'translations'	=> [
                    [
                        'locale'		=> 'en',
                        'country_name'	=> 'Egypt'
                    ],
                    [
                        'locale'		=> 'ar',
                        'country_name'	=> 'مصر'
                    ]
                ],
                'cities'	=> [
                    [
                        'status'		=> '1',
                        'tax'			=> 10,
                        'shipping'		=> 10,
                        'translations'	=> [
                            [
                                'locale'		=> 'en',
                                'city_name'	=> 'Cairo'
                            ],
                            [
                                'locale'		=> 'ar',
                                'city_name'	=> 'القاهرة'
                            ]
                        ]
                    ],
                    [
                        'status'		=> '1',
                        'tax'			=> 10,
                        'shipping'		=> 15,
                        'translations'	=> [
                            [
                                'locale'		=> 'en',
                                'city_name'	=> 'Mansoura'
                            ],
                            [
                                'locale'		=> 'ar',
                                'city_name'	=> 'المنصورة'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'status'	=> '1',
                'code'		=> '962',
                'currency'  => 'JOD',
                'translations'	=> [
                    [
                        'locale'		=> 'en',
                        'country_name'	=> 'Jorden'
                    ],
                    [
                        'locale'		=> 'ar',
                        'country_name'	=> 'الأردن'
                    ]
                ],
                'cities'	=> [
                    [
                        'status'		=> '1',
                        'tax'			=> 10,
                        'shipping'		=> 10,
                        'translations'	=> [
                            [
                                'locale'		=> 'en',
                                'city_name'	=> 'Amman'
                            ],
                            [
                                'locale'		=> 'ar',
                                'city_name'	=> 'عمان'
                            ]
                        ]
                    ],
                    [
                        'status'		=> '1',
                        'tax'			=> 10,
                        'shipping'		=> 20,
                        'translations'	=> [
                            [
                                'locale'		=> 'en',
                                'city_name'	=> 'Az Zarqa'
                            ],
                            [
                                'locale'		=> 'ar',
                                'city_name'	=> 'الزرقاء'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        foreach ($countries as $country) {
            $createdCountry = Country::firstOrCreate(['status' => $country['status'], 'code' => $country['code'], 'currency' => $country['currency']]);

            foreach ($country['translations'] as $translation) {
                $translation['country_id']	= $createdCountry->id;
                CountryTranslation::firstOrCreate($translation);
            }

            foreach ($country['cities'] as $city) {
                $createdCity = $createdCountry->cities()->firstOrCreate(['status' => $city['status'], 'tax' => $city['tax'], 'shipping' => $city['shipping']]);

                foreach ($city['translations'] as $translation) {
                    $translation['city_id']	= $createdCity->id;
                    CityTranslation::firstOrCreate($translation);
                }
            }
        }
    }
}
