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
        $this->triprequest();
    }

    public function triprequest() {

        $timeNow = time();
        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();

        $triprequests = TripRequest::whereIn('status', [2, 4])->get();

        $image = "image/tripaccept.png";
        foreach ($triprequests as $triprequest) {
            $timeInterval = ($timeNow - strtotime($triprequest->created_at))/60;
            if ($timeInterval > 4.5) {
                $triprequest->update(['status' => 5]);
                $customer = Customer::where('id', $triprequest->customer_id)->first();

                $driver_id = $database->getReference('/tripreques/'. $triprequest)->getSnapshot()->getValue();

                $newPost = $database
                ->getReference('/triprequest/' . $triprequest->id)
                ->remove();

                $newPost = $database
                ->getReference('/drivers/' . $driver_id['driver_id'])
                ->update([
                    'booking_status' => 0
                ]);

                if ($customer->fcm_token) {
                    $this->send_fcm('لم يتم العثور على سائق', 'لا يتوفر حالياً سائقين', $customer->fcm_token);
                }
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
