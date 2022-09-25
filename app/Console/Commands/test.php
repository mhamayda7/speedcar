<?php

namespace App\Console\Commands;

use App\Customer;
use App\Driver;
use App\Models\DriverTripRequest;
use App\Models\TripRequest;
use Exception;
use Illuminate\Console\Command;
use Kreait\Firebase\Factory;

class test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command test';

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
        $this->runChangeDrive();
    }

    public function changeDrivers()
    {
        try {
            $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'))
                ->withServiceAccount(config_path() . '/' . env('FIREBASE_FILE'));
            $database = $factory->createDatabase();

            $trip_requests = $database->getReference('/triprequest/')
                ->getValue();

            foreach ($trip_requests as $trip_request) {
                $this->getrequest($trip_request['request_id'], $trip_request['driver_id']);
            }
        } catch (Exception $e) {
        }
    }

    public function getrequest($trip_id, $driver_id)
    {
        $trip = TripRequest::where('id', $trip_id)->first();

        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();

        $newPost = $database
            ->getReference('/drivers/' . $driver_id)
            ->update([
                'booking_status' => 0
            ]);

        if ($trip->booking_type == 1) {
            TripRequest::where('id', $trip_id)->update(['status' => 4]);
        }

        $newcaptin = $this->find_car($trip_id);

        $data['driver_id'] = $driver_id;
        $data['trip_request_id'] = $trip_id;
        $data['status'] = 0;

        DriverTripRequest::create($data);
        return response()->json([
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function find_car($trip_request_id)
    {

        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();

        $trip_request = TripRequest::where('id', $trip_request_id)->first();
        $drivers = $database->getReference('/drivers/')->getSnapshot()->getValue();
        $rejected_drivers = DriverTripRequest::where('trip_request_id', $trip_request_id)->where('status', 0)->pluck('driver_id')->toArray();
        $min_distance = 0;
        $min_driver_id = 0;

        foreach ($drivers as $key => $value) {
            if (!in_array($value['driver_id'], $rejected_drivers)) {
                if ($value['online_status'] == 1 && $value['booking_status'] == 0) {
                    $amount = Driver::where('id', $value['driver_id'])->value('wallet');
                    if ($amount > (-1)) {
                        $distance = $this->distance($trip_request->pickup_lat, $trip_request->pickup_lng, $value['lat'], $value['lng'], 'K');
                        if ($min_distance == 0) {
                            $min_distance = $distance;
                            $min_driver_id = $value['driver_id'];
                        } else if ($distance < $min_distance) {
                            $min_distance = $distance;
                            $min_driver_id = $value['driver_id'];
                        }
                    }
                }
            }
        }

        if ($min_driver_id != 0) {
            $fcm = Driver::where('id', $min_driver_id)->value('fcm_token');
            if ($fcm) {
                try {
                    $this->send_fcm('لديك طلب جديد', 'لديك طلب رحلة جديد', $fcm);
                } catch (Exception $e) {
                }
            }
            $newPost = $database
                ->getReference('/drivers/' . $min_driver_id)
                ->update([
                    'booking_status' => 0
                ]);
        }

        if ($min_driver_id == 0) {

            $trip_request->update(['status' => 5]);
            $custmoer_fcm = Customer::where('id', $trip_request->customer_id)->value('fcm_token');
            try {
                $this->send_fcm('نأسف جميع الكباتن مشغولين', 'جميع الكباتن مشغولين في رحلات أخرى', $custmoer_fcm);
            } catch (Exception $e) {
            }
            $newPost = $database
                ->getReference('/customers/' . $trip_request->customer_id)
                ->update([
                    'booking_id' => 0,
                    'booking_status' => 0
                ]);

            $newPost = $database
                ->getReference('/triprequest/' . $trip_request->id)
                ->remove();
        } else {
            $newPost = $database

                ->getReference('/triprequest/' . $trip_request->id)
                ->update([
                    'driver_id' => $min_driver_id,
                ]);

            return $trip_request->id;
        }
    }

    public function runChangeDrive() {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,"https://speedcar-jo.com/api/speedV1/changeDrive");
        curl_setopt($ch, CURLOPT_POST, 1);
        // curl_setopt($ch, CURLOPT_POSTFIELDS);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        curl_close ($ch);

        // Further processing ...
        // if ($server_output == "OK") { ... } else { ... }
        // sleep(time() % 30);
        // $curl = curl_init();
        // curl_setopt_array($curl, array(
        //     CURLOPT_URL => "https://speedcar-jo.com/api/speedV1/changeDrive",
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => "",
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 0,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => "POST",
        //     CURLOPT_HTTPHEADER => array(
        //         "Authorization: Bearer qrVfXQ54M5MBDPPuVtUzUoxM0Fnh7hq9ULkSj8r9"
        //     ),
        // ));
    }
}
