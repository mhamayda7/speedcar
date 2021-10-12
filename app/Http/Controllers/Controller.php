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
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;
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
        $message = mb_convert_encoding($input['message'], "UTF-8");
        // dd($message);
        $result = $this->smsSe($input['phone'],$message);
        return response()->json([
            "result" => $result,
            "message" => 'Succsess Send sms',
            "status" => 1
        ]);

    }

    public function smsSe($phone, $message)
    {
        $url = "https://gatewayapi.com/rest/mtsms";
        $api_token = "yC5hi64NSEm9P1lfR2ouiEC3IqyQS2XVuvIo3xtAay0-lMGb_DrPV7QPgGz57BEx";

        //Set SMS recipients and content
        $recipients = [$phone];
        $json = [
            'sender' => 'SpeedCar',
            'message' => $message,
            'recipients' => [],
            // 'encoding' => 'UTF-8',
        ];
        foreach ($recipients as $msisdn) {
            $json['recipients'][] = ['msisdn' => $msisdn];
        }

        //Make and execute the http request
        //Using the built-in 'curl' library
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        curl_setopt($ch, CURLOPT_USERPWD, $api_token . ":");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
        // curl_setopt($ch, CURLOPT_ENCODING, "UCS2");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return  $result;
        // $json = json_decode($result);
        // print_r($json->ids);
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



}
