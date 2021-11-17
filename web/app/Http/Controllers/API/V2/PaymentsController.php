<?php

namespace App\Http\Controllers\API\V2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Validation\Rule;
use Validator;

class PaymentsController extends Controller
{

  /**
      * @SWG\GET(
      *     path="/api/v2/user/cards",
      *     description="get user cards",
      *     tags = {"payments"},
      *   security={{"Bearer":{}}},
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

    public function getCards()
    {
        $cards = auth()->user()->cards()->get();
        return response()->json(['status' => true, 'data' => $cards]);
    }


    /**
        * @SWG\Post(
        *     path="/api/v2/user/cards",
        *     description="create user card",
        *     tags = {"payments"},
        *   security={{"Bearer":{}}},
        *     @SWG\Parameter(
        *         name="card_token",
        *         in="query",
        *         type="string",
        *         description="card_token(tok_visa)",
        *         required=true,
        *     ),
        *     @SWG\Parameter(
        *         name="exp_month",
        *         in="query",
        *         type="string",
        *         description="exp_month",
        *         required=true,
        *     ),
        *     @SWG\Parameter(
        *         name="exp_year",
        *         in="query",
        *         type="string",
        *         description="exp_year",
        *         required=true,
        *     ),
        *     @SWG\Parameter(
        *         name="last4",
        *         in="query",
        *         type="string",
        *         description="last4",
        *         required=true,
        *     ),
        *     @SWG\Parameter(
        *         name="brand",
        *         in="query",
        *         type="string",
        *         description="brand visa",
        *         required=true,
        *     ),
        *     @SWG\Parameter(
        *         name="default",
        *         in="query",
        *         type="integer",
        *         description="default 0 => false, 1 => true",
        *         required=true,
        *     ),
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

    public function createCard()
    {

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
        if ($inputs['default'] == 1) {
            $this->setDefaultCard($card->id);
        }

        $response = auth()->user()->cards()->find($card->id);

        // return success response
        return response()->json(['status'=>true,'msg' => 'user card created successfully', 'data' => $response], 201);
    }

    /**
        * @SWG\PUT(
        *     path="/api/v2/user/cards/1/default",
        *     description="set payment as default card",
        *     tags = {"payments"},
        *   security={{"Bearer":{}}},
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

    public function defaultCard($card_id)
    {
        $card = auth()->user()->cards()->find($card_id);
        if (empty($card)) {
            return response()->json(['status' => false, 'msg' => 'card not found'], 404);
        }

        $this->setDefaultCard($card_id);
        return response()->json(['status'=>true,'msg' => 'user card updated successfully']);
    }

    private function setDefaultCard($card_id)
    {
        auth()->user()->cards()->update(['default' => 0]);
        auth()->user()->cards()->find($card_id)->update(['default' => 1]);
    }


    /**
        * @SWG\DELETE(
        *     path="/api/v2/user/cards/1",
        *     description="deleteCard user card",
        *     tags = {"payments"},
        *   security={{"Bearer":{}}},
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

    public function deleteCard($card_id)
    {
        $card = auth()->user()->cards()->find($card_id);
        if (empty($card)) {
            return response()->json(['status' => false, 'msg' => 'card not found'], 404);
        }

        $card->delete();
        return response()->json(['status' => true, 'msg' => 'card deleted successfully'], 200);
    }
}
