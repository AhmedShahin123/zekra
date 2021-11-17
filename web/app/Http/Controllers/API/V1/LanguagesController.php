<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;

use App\Models\Language;

use Validator;

class LanguagesController extends Controller
{
    public function getLanguages(){
        $languages = Language::all();
        return response()->json(['status' => true, 'data' => $languages]);
    }
}
