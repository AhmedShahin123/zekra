<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Validation\Rule;
use Validator;

class PaymentsController extends Controller
{

    public function getCards(){
        $cards = auth()->user()->cards()->get();
        return response()->json(['status' => true, 'data' => $cards]);
    }

    public function createCard(){

        // set the validation rules and validate the user input
        $validator = Validator::make(request()->all(), [
            'card_token'    => 'required|unique:user_cards,card_token',
            'exp_month'     => 'required',
            'exp_year'      => 'required',
            'last4'         => 'required',
            'brand'         => 'required',
            'default'       => ['required', Rule::in(['0', '1'])],
        ]);

        // return an error message in case validation fails
        if ($validator->fails()) {
            return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 400);
        }

        $inputs = request()->only('card_token', 'exp_month', 'exp_year', 'last4', 'default', 'brand');

        // update auth user info
        $card = auth()->user()->cards()->create($inputs);
        if($inputs['default'] == 1){
            $this->setDefaultCard($card->id);
        }

        $response = auth()->user()->cards()->find($card->id);

        // return success response
        return response()->json(['status'=>true,'msg' => 'user card created successfully', 'data' => $response], 201);
    }

    public function defaultCard($card_id){
        $card = auth()->user()->cards()->find($card_id);
        if(empty($card)){
            return response()->json(['status' => false, 'msg' => 'card not found'], 404);
        }

        $this->setDefaultCard($card_id);
        return response()->json(['status'=>true,'msg' => 'user card updated successfully']);
    }

    private function setDefaultCard($card_id){
        auth()->user()->cards()->update(['default' => 0]);
        auth()->user()->cards()->find($card_id)->update(['default' => 1]);
    }

    public function deleteCard($card_id){
        $card = auth()->user()->cards()->find($card_id);
        if(empty($card)){
            return response()->json(['status' => false, 'msg' => 'card not found'], 404);
        }

        $card->delete();
        return response()->json(['status' => true, 'msg' => 'card deleted successfully'], 200);
    }
}
