<?php

use Illuminate\Database\Seeder;
use App\Models\Setting;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      	$settings = [
			['key' 	=> 'album_price', 'value' => 100, 'type' => 'number', 'name' => 'Album Price', 'section' => 'Album Management'],
            ['key'	=> 'cover_photo', 'value' => 'cover.jpg', 'type' => 'image', 'name' => 'Album Cover Photo', 'section' => 'Album Management'],
            ['key'	=> 'invitation_coupon_value', 'value' => '10', 'type' => 'number', 'name' => 'Invitation coupon discount value', 'section' => 'Invitation Coupons'],
            ['key'	=> 'invitation_coupon_usage_times', 'value' => '3', 'type' => 'number', 'name' => 'Invitation coupon usage times', 'section' => 'Invitation Coupons'],
            ['key'	=> 'invitation_coupon_value_type', 'value' => 'money', 'type' => 'select', 'name' => 'Invitation coupon value type', 'section' => 'Invitation Coupons', 'extra_data' => ['options' => ['money', 'points']]]

        ];
		
		foreach($settings as $setting){
            if(Setting::where('key', $setting['key'])->count() == 0){
                if($setting['type'] == 'image'){
                    //upload the image
                    $imageName = Storage::putFile('settings', new File(public_path('images/default/'.$setting['value'])));
                    $setting['value'] = $imageName;
                }
                Setting::create($setting);
            }
      	}
    }
}
