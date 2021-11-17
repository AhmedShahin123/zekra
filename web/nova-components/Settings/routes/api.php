<?php

use App\Http\Resources\Setting as SettingResource;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
/*
|--------------------------------------------------------------------------
| Tool API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your tool. These routes
| are loaded by the ServiceProvider of your tool. They are protected
| by your tool's "Authorize" middleware by default. Now, go build!
|
*/

Route::get('/', function (Request $request) {
    $settings = Setting::all();
    $response = SettingResource::collection($settings);
    return response()->json($response);
});

Route::put('/{id}', function (Request $request, $id) {
    $rules = [
        'name'  => 'required',
        'value' => 'required'
    ];
    if($request->hasFile('value')){
        $rules = [
            'name'  => 'required',
            'value' => 'required|image'
        ];
    }
    $validator = Validator::make($request->all(), $rules);
    if($validator->fails()){
        return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 400);
    }

    $inputs = $request->only('name', 'value');
    if($request->hasFile('value')){
        // get old image to delete it after uploading the new one
        $old = Setting::find($request->get('id'));

        $file = $request->file('value');
        $imageName = Storage::put("settings",$file);
        $inputs['value'] = $imageName;

        // delete the old image
        if($old){
            Storage::delete($old->value);
        }
        
    }
    $update = Setting::find($id)->update($inputs);
    if($update){
        $setting = Setting::find($id);
        $response = new SettingResource($setting);
        return response()->json(['status' => true, 'msg' => 'Settings updated successfully', 'data' => $response]);
    }else{
        return response()->json(['status' => false, 'msg' => 'Something went wrong'], 400);
    }
});
