<?php

namespace App\Console\Commands;

use App\Customer;
use App\Models\TripRequestStatus;
use App\NotificationMessage;
use App\TripRequest;
use Illuminate\Console\Command;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\CloudMessage;

class timeoutTripRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'TripRequest:timeout';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'timeoutTripRequest';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $timeNow = time();
        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();
        $triprequests = TripRequest::where('status', 2)->get();
        $image = "image/tripaccept.png";
        foreach ($triprequests as $triprequest) {
            $timeInterval = ($timeNow - strtotime($triprequest->created_at))/60;
            if ($timeInterval > 1) {
                $triprequest->update(['status' => 4]);
                $customer = Customer::where('id', $triprequest->customer_id)->first();
                if ($customer->fcm_token) {
                    $current_status = TripRequestStatus::where('id', 5)->first();
                    $new_status = TripRequestStatus::where('id', 5)->first();
                    $this->save_notifcation($customer->id,1,$current_status->status_name,$current_status->customer_status_name,$image);
                    $this->send_fcm($current_status->status_name, $current_status->customer_status_name, $customer->fcm_token);
                }

                $newPost = $database
                ->getReference('/triprequest/' . $triprequest->id)
                ->remove();
            }
        }
    }

    public function save_notifcation($id, $type, $title, $message, $image)
    {
        $data = [];
        $data['user_id'] = $id;
        $data['country_id'] = 1;
        $data['type'] = $type;
        $data['title'] = $title;
        $data['message'] = $message;
        $data['image'] = $image;
        $data['status'] = 1;
        NotificationMessage::create($data);
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
}
