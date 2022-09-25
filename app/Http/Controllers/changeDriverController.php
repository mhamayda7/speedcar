<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Driver;
use App\Models\DriverTripRequest;
use App\Models\TripRequest;
use Exception;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;

class changeDriverController extends Controller
{

    public function changeDrivers()
    {
        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'))
            ->withServiceAccount(config_path() . '/' . env('FIREBASE_FILE'));
        $database = $factory->createDatabase();

        try {
            $tripRequests = $database->getReference('/triprequest/')->getValue();

            foreach ($tripRequests as $key => $tripRequest) {
                // $trip_request = TripRequest::where('id', $tripRequest['request_id'])->first();
                // $driver = $database->getReference('/drivers/'.$tripRequest['driver_id'])->getSnapshot()->getValue();
                $this->newDriver($tripRequest['request_id'], $tripRequest['driver_id']);
                // dd($trip_request->pickup_lat. $trip_request->pickup_lng. $driver['lat']. $driver['lng']);
                // $distance = $this->distance($trip_request->pickup_lat, $trip_request->pickup_lng, $driver['lat'], $driver['lng'], 'K');
                // dd($distance);
            }
        } catch (Exception $e) {
        }
    }

    public function newDriver($requestID, $driverID)
    {
        $data['driver_id'] = $driverID;
        $data['trip_request_id'] = $requestID;
        $data['status'] = 0;
        $rejctDriver = DriverTripRequest::create($data);

        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();

        $trip_request = TripRequest::where('id', $requestID)->first();
        $drivers = $database->getReference('/drivers/')->getSnapshot()->getValue();
        // dd($drivers);
        $rejected_drivers = DriverTripRequest::where('trip_request_id', $requestID)->where('status', 0)->pluck('driver_id')->toArray();

        $min_distance = 0;
        $min_driver_id = 0;

        foreach ($drivers as $key => $value) {
            if (isset($value)) {
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
        }

        if ($min_driver_id == 0 || $min_driver_id  == $driverID) {

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
        } elseif ($min_driver_id != 0) {
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
                    'booking_status' => 1
                ]);
        } else {
            return false;
        }
    }
    // try {
    //     $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'))
    //         ->withServiceAccount(config_path() . '/' . env('FIREBASE_FILE'));
    //     $database = $factory->createDatabase();

    //     $trip_requests = $database->getReference('/triprequest/')
    //         ->getValue();

    //     foreach ($trip_requests as $trip_request) {
    //         // $timer = $trip_request['time'];
    //         $newPost2 = $database
    //             ->getReference('/triprequest/' . $trip_request['request_id'])
    //             ->update([
    //                 'time' => $trip_request['time'] + 2
    //             ]);

    //         $tt = $this->getrequest($trip_request['request_id'], $trip_request['driver_id']);
    //         dd("good");
    //     }
    // } catch (Exception $e) {
    // }


    // public function getrequest($trip_id, $driver_id)
    // {
    //     $trip = TripRequest::where('id', $trip_id)->first();

    //     $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
    //     $database = $factory->createDatabase();

    //     $newPost = $database
    //         ->getReference('/drivers/' . $driver_id)
    //         ->update([
    //             'booking_status' => 0
    //         ]);

    //     if ($trip->booking_type == 1) {
    //         TripRequest::where('id', $trip_id)->update(['status' => 4]);
    //     }

    //     $newcaptin = $this->find_car($trip_id);

    //     $data['driver_id'] = $driver_id;
    //     $data['trip_request_id'] = $trip_id;
    //     $data['status'] = 0;

    //     DriverTripRequest::create($data);
    //     // return response()->json([
    //     //     "message" => 'Success',
    //     //     "status" => 1
    //     // ]);
    // }

    // public function find_car($trip_request_id)
    // {

    //     $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
    //     $database = $factory->createDatabase();

    //     $trip_request = TripRequest::where('id', $trip_request_id)->first();
    //     $drivers = $database->getReference('/drivers/')->getSnapshot()->getValue();
    //     $rejected_drivers = DriverTripRequest::where('trip_request_id', $trip_request_id)->where('status', 0)->pluck('driver_id')->toArray();

    //     $min_distance = 0;
    //     $min_driver_id = 0;
    //     $oldDriver = $database->getReference('/triprequest/' . $trip_request_id)->getSnapshot()->getValue()['driver_id'];

    //     foreach ($drivers as $key => $value) {
    //         if (!in_array($value['driver_id'], $rejected_drivers)) {
    //             if ($value['online_status'] == 1 && $value['booking_status'] == 0) {
    //                 $amount = Driver::where('id', $value['driver_id'])->value('wallet');
    //                 if ($amount > (-1)) {
    //                     $distance = $this->distance($trip_request->pickup_lat, $trip_request->pickup_lng, $value['lat'], $value['lng'], 'K');
    //                     if ($min_distance == 0) {
    //                         $min_distance = $distance;
    //                         $min_driver_id = $value['driver_id'];
    //                     } else if ($distance < $min_distance) {
    //                         $min_distance = $distance;
    //                         $min_driver_id = $value['driver_id'];
    //                     }
    //                 }
    //             }
    //         }
    //     }
    //     dd($min_driver_id);
    //     if ($min_driver_id == 0 || $min_driver_id  == $oldDriver) {

    //         $trip_request->update(['status' => 5]);
    //         $custmoer_fcm = Customer::where('id', $trip_request->customer_id)->value('fcm_token');
    //         try {
    //             $this->send_fcm('نأسف جميع الكباتن مشغولين', 'جميع الكباتن مشغولين في رحلات أخرى', $custmoer_fcm);
    //         } catch (Exception $e) {
    //         }
    //         $newPost = $database
    //             ->getReference('/customers/' . $trip_request->customer_id)
    //             ->update([
    //                 'booking_id' => 0,
    //                 'booking_status' => 0
    //             ]);

    //         $newPost = $database
    //             ->getReference('/triprequest/' . $trip_request->id)
    //             ->remove();
    //     } elseif ($min_driver_id != 0) {
    //         $fcm = Driver::where('id', $min_driver_id)->value('fcm_token');
    //         if ($fcm) {
    //             try {
    //                 $this->send_fcm('لديك طلب جديد', 'لديك طلب رحلة جديد', $fcm);
    //             } catch (Exception $e) {
    //             }
    //         }
    //         $newPost = $database
    //             ->getReference('/drivers/' . $min_driver_id)
    //             ->update([
    //                 'booking_status' => 1
    //             ]);
    //     } else {
    //         return false;
    //     }
    // }

    function distance($lat1, $lon1, $lat2, $lon2, $unit)
    {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }

    // $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'))
    //     ->withServiceAccount(config_path() . '/' . env('FIREBASE_FILE'));
    // $database = $factory->createDatabase();
    // $drivers = Driver::all();
    // foreach ($drivers as $key => $driver) {
    //     $newPost = $database
    //     ->getReference('/drivers/' . $driver->id)
    //     ->update([
    //         'accuracy' => 0,
    //         'booking_status' => 0,
    //         'distance' => 0,
    //         'driver_id' => $driver->id,
    //         'driver_name' => $driver->full_name,
    //         'heading' => 0,
    //         'lat' => 0,
    //         'lng' => 0,
    //         'online_status' => 0,
    //         'startlat' => 0,
    //         'startlng' => 0,
    //         'status' => $driver->status,
    //     ]);
    // }
}
