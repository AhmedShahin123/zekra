<?php

use App\Models\City;
use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cities = City::all();
        if(Product::count() == 0){
            $productsData = [
                [
                    'weight'            => mt_rand(100, 1000),
                    'dimensions'        => '30*50',
                    'album_template'    => Storage::putFile('products', new File(public_path('images/default/template.jpg'))),
                    'status'            => 'available'
                ]
            ];
    
            foreach($productsData as $productData){
                $product = Product::create($productData);
                foreach($cities as $city){
                    $productPriceData = [
                        'product_id'    => $product->id,
                        'city_id'       => $city->id,
                        'country_id'    => $city->country_id,
                        'price'         => mt_rand(60, 500)
                    ];
                    ProductPrice::create($productPriceData);
                }
            }
        }
    }
}
