<?php

namespace App\Http\Controllers\API\V2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;

use App\Models\Language;

use Validator;

class LanguagesController extends Controller
{
    /**
        * @SWG\GET(
        *     path="/api/v2/languages",
        *     description="get available languages",
        *     tags = {"languages"},
        *     @SWG\Response(
        *         response=200,
        *         description="",
        *        examples={
        *     "application/json": { "status": true, "data": {} }
        *      }
        *     ),
        *     @SWG\Response(
        *         response=401,
        *         description="",
        *        examples={
        *     "application/json": { "status": false, "msg": "Unauthorized" }
        *      }
        *     )
        *  )
        */
    public function getLanguages()
    {
        $languages = Language::all();
        return response()->json(['status' => true, 'data' => $languages]);
    }
}
