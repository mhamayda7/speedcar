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

        $code = PromoCode::select('id', 'promo_code', 'promo_type', 'discount', 'description', 'status')->where('promo_code', $input['promo'])->first();
        if(null !== ($code)) {
            return response()->json([
                "result" => $code,
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
