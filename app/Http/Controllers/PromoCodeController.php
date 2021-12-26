<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PromoCode;
use App\UserPromoHistory;
use DateTime;
use Illuminate\Support\Facades\Auth;
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


        $promo = PromoCode::where('promo_code', $input['promo'])->first();
        $used_code = UserPromoHistory::where('customer_id', Auth::user()->id)->get();
        if (isset($promo)) {
            $date_today = date("Y-m-d H:i:s");
            $promo_valid = date("Y-m-d H:i:s", strtotime($promo->valid_to));
            if($promo->status && $promo_valid > $date_today) {
                if (count($used_code) <= $promo->count_used) {
                    return response()->json([
                        "promo" => $promo,
                        "message" => 'تمتع بالخصم',
                        "status" => 1
                    ]);
                } else {
                    return response()->json([
                        "message" => 'لقد تجاوزت عدد مرات الاستخدام',
                        "status" => 0
                    ]);
                }
            } else {
                return response()->json([
                    "message" => 'كود الخصم غير فعال',
                    "status" => 0
                ]);
            }
        } else {
            return response()->json([
                "message" => 'كود الخصم غير موجود',
                "status" => 1
            ]);
        }
    }

}
