<?php

namespace App\Console\Commands;


use App\Customer;
use App\Models\DriverTripRequest;
use App\Driver;
use App\TripRequest;
use Kreait\Firebase\Factory;
use Exception;
use Illuminate\Console\Command;

class ChangeDriver extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ChangeDriverForRequest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ChangeDriverForRequest';

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
        try {
            $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
            $database = $factory->createDatabase();

            $trip_requests = $database->getReference('/triprequest/')
                ->getValue();

            foreach ($trip_requests as $trip_request) {
                $this->getrequest($trip_request['request_id'], $trip_request['driver_id']);
            }
        } catch (Exception $e) {
        }
        sleep(18);
        try {
            $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
            $database = $factory->createDatabase();

            $trip_requests = $database->getReference('/triprequest/')
                ->getValue();

            foreach ($trip_requests as $trip_request) {
                $this->getrequest($trip_request['request_id'], $trip_request['driver_id']);
            }
        } catch (Exception $e) {
        }
        sleep(18);
        try {
            $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
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
        $input['driver_id'] = $driver_id;

        $trip = TripRequest::where('id', $trip_id)->first();

        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();

        $newPost = $database
            ->getReference('/drivers/' . $input['driver_id'])
            ->update([
                'booking_status' => 0
            ]);

        if ($trip->booking_type == 1) {
            TripRequest::where('id', $trip_id)->update(['status' => 4]);
        }

        $data['driver_id'] = $input['driver_id'];
        $data['trip_request_id'] = $trip_id;
        $data['status'] = 0;

        DriverTripRequest::create($data);
        $this->find_car($trip_id);
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
                $amount = Driver::where('id', $value['driver_id'])->value('wallet');
                if ($amount > (-1)) {
                    if ($value['online_status'] == 1 && $value['booking_status'] == 0) {
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
}
