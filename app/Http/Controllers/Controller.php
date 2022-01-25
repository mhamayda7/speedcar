<?php

namespace App\Http\Controllers;
use Validator;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Twilio\Rest\Client;
use Mail;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function sendError($message)
    {
        $message = $message->all();
        $response['error'] = "validation_error";
        $response['message'] = implode('', $message);
        $response['status'] = "0";
        return response()->json($response, 200);
    }

    public function send_order_mail($mail_header, $subject, $to_mail)
    {
        Mail::send('mail_templates.trip_invoice', $mail_header, function ($message)
        use ($subject, $to_mail) {
            $message->from(env('MAIL_USERNAME'), env('APP_NAME'));
            $message->subject($subject);
            $message->to($to_mail);
        });
    }

    public function send_fcm($title, $description, $token)
    {
        $factory = (new Factory)->withServiceAccount(config_path().'/'.env('FIREBASE_FILE'));
        $messaging = $factory->createMessaging();
        $message = CloudMessage::fromArray([
            'token' => $token,
            'notification' => [],
            'data' => [],
        ]);

        $config = AndroidConfig::fromArray([
            'ttl' => '3600s',
            'priority' => 'normal',
            'notification' => [
                'title' => $title,
                'body' => $description,
                'icon' => '',
                'color' => '',
            ],
        ]);
        $message = $message->withAndroidConfig($config);
        $messaging->send($message);
    }

    public function send_fcmAll(Request $request)
    {
        $input = $request->all();
        $factory = (new Factory)->withServiceAccount(config_path().'/'.env('FIREBASE_FILE'));
        $messaging = $factory->createMessaging();
        $topic = 'all';
        $message = CloudMessage::withTarget('topic', $topic)
            ->withNotification([
                'title' => $input['title'],
                'body' => $input['description'],
                'image' => $input['icon'],
                // 'color' => '#f45342',
                'sound' => 'default',
            ]);
        ;
        $messaging->send($message);
    }

    public function sendSms($phone_number, $message)
    {
        $sid    = env('TWILIO_SID');
        $token  = env('TWILIO_TOKEN');
        $client = new Client($sid, $token);
        $client->messages->create($phone_number, ['from' => env('TWILIO_FROM'), 'body' => $message,]);
        return true;
    }

    public function ride_completeion($mail_header, $subject, $to_mail)
    {
        Mail::send('mail_templates.ride_completeion_mail', $mail_header, function ($message)
        use ($subject, $to_mail) {
            $message->from(env('MAIL_USERNAME'), env('APP_NAME'));
            $message->subject($subject);
            $message->to($to_mail);
        });
    }

    public function sendS(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'phone' => 'required',
            'message' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $this->smsSe($input['phone'],$input['message']);
        return response()->json([
            "message" => 'تم ارسال الرسالة بنجاح',
            "status" => 1
        ]);

    }

    public function smsSe($phone, $message)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.releans.com/v2/message",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "sender=Speed Car&mobile=" . $phone . "&content=".$message,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer cce5233e61cc8a939d8d9670e5c59d2a"
            ),
        ));
        curl_exec($curl);
        curl_close($curl);
        return 1;
    }

    public function splash()
    {
        $data = DB::table('splash')->get()->first();
        return response()->json([
            "result" => $data,
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function get_supplier() {
        $data = DB::table('supplier_detailes')->get();
        return response()->json([
            "result" => $data,
            "message" => 'Success',
            "status" => 1
        ]);
    }

}
