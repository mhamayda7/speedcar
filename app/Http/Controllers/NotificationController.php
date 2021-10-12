<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\NotificationMessage;
use Illuminate\Support\Facades\Auth;
use Validator;

class NotificationController extends Controller
{
    public function get_customer_notification_messages(Request $request)
    {
        $id = Auth::user()->id;
        $data = NotificationMessage::where('status',1)->where('type',1)->whereIn('user_id',[$id,0])->orderBy('id', 'DESC')->get();

        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function get_driver_notification_messages(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [

        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $input['driver_id'] = Auth::user()->id;
        $data = NotificationMessage::where('status',1)->where('type',2)->whereIn('user_id',[$input['driver_id'],0])->orderBy('id', 'DESC')->get();

        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }



    public function sendError($message)
    {
        $message = $message->all();
        $response['error'] = "validation_error";
        $response['message'] = implode('',$message);
        $response['status'] = "0";
        return response()->json($response, 200);
    }
}
