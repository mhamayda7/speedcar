<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PromoCode;
use Validator;

class PromoCodeController extends Controller
{
    public function promo()
    {
        $data = PromoCode::where('status',1)->get();

        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function check_promo(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'promo' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        if(null !== (PromoCode::where('promo_code', $input['promo'])->first())) {
            return response()->json([
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'failed',
                "status" => 0
            ]);
        }


    }

}
