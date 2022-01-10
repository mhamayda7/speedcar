<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Faq;
use App\Trip;
use App\Customer;
use App\AppSetting;
use App\Country;
use App\Status;
use App\Currency;
use App\Models\DriverTripRequest;
use App\UserPromoHistory;
use App\PaymentMethod;
use App\DriverEarning;
use App\DriverWalletHistory;
use App\BookingStatus;
use App\CancellationSetting;
use App\PaymentHistory;
use App\Driver;
use App\TripCancellation;
use App\DriverVehicle;
use App\TripSetting;
use App\TripRequest;
use App\Transaction;
use App\UserType;
use App\Models\ScratchCardSetting;
use App\Models\CustomerOffer;
use App\Models\LuckyOffer;
use App\InstantOffer;
use App\TripType;
use Validator;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;
use DateTime;
use DateTimeZone;
use App\CustomerWalletHistory;
use App\Models\DailyFareManagement;
use App\Models\Point;
use App\Models\RateTrip;
use App\Models\TripRequestStatus;
use App\NotificationMessage;
use App\PromoCode;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class BookingController extends Controller
{
    // public function ride_confirm(Request $request)
    // {
    //     $input = $request->all();
    //     $validator = Validator::make($input, [
    //         // 'km' => 'required',
    //         'vehicle_type' => 'required',
    //         // 'customer_id' => 'required',
    //         'promo' => 'required',
    //         'country_id' => 'required',
    //         'pickup_address' => 'required',
    //         'pickup_date' => 'required',
    //         'pickup_lat' => 'required',
    //         'pickup_lng' => 'required',
    //         'trip_type' => 'required',
    //         'drop_address' => 'required',
    //         'drop_lat' => 'required',
    //         'drop_lng' => 'required',
    //     ]);
    //     if ($validator->fails()) {
    //         return $this->sendError($validator->errors());
    //     }

    //     $input['customer_id'] = Auth::user()->id;

    //     $input['pickup_date'] = date("Y-m-d H:i:s", strtotime($input['pickup_date']));
    //     $current_date = $this->get_date($input['country_id']);
    //     $interval_time = $this->date_difference($input['pickup_date'], $current_date);
    //     if ($interval_time <= 30) {
    //         $input['booking_type'] = 1;
    //         $input['status'] = 1;
    //     } else {
    //         $input['booking_type'] = 2;
    //         $input['status'] = 2;
    //     }
    //     //$factory = (new Factory)->withServiceAccount(config_path().'/'.env('FIREBASE_FILE'));
    //     $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'))->withServiceAccount(config_path().'/'.env('FIREBASE_FILE'));
    //     $database = $factory->createDatabase();

    //     $drivers = $database->getReference('/vehicles/' . $input['vehicle_type'])
    //     ->getSnapshot()->getValue();

    //     $i=1;
    //     $min_distance = 0;
    //     $min_driver_id = 0;
    //     $booking_searching_radius = TripSetting::value('booking_searching_radius');


    //     foreach ($drivers as $key => $value) {
    //         if ($value && array_key_exists('gender', $value)) {
    //                 $distance = $this->distance($input['pickup_lat'], $input['pickup_lng'], $value['lat'], $value['lng'], 'K');
    //                 if ($distance <= $booking_searching_radius && $value['online_status'] == 1 && $value['booking_status'] == 0) {
    //                     $driver_selected[$i] = $value['driver_id'];

    //                     if ($min_distance == 0) {
    //                         $min_distance = $distance;
    //                         $min_driver_id = $value['driver_id'];
    //                     } else if ($distance < $min_distance) {
    //                         $min_distance = $distance;
    //                         $min_driver_id = $value['driver_id'];
    //                     }
    //                 }
    //             $i++;
    //         }
    //     }
    //     dd($driver_selected);
    //     // $drivers = $database->getReference('/vehicles/' . 1)
    //     // ->getSnapshot()->getValue();
    //     // $driver_selected = [];
    //     // $i=1;

    //     // foreach ($drivers as $key => $value) {
    //     //     // $test = $value['booking_status'];
    //     //     if($value['booking_status'] == 0 && $value['online_status'] == 1){
    //     //         $driver_selected[$i] = $value['driver_id'];
    //     //     }
    //     //     $i++;
    //     // }


    //     $input['km'] = $distance;
    //     if ($min_driver_id == 0) {
    //         return response()->json([
    //             "message" => 'Sorry drivers not available right now',
    //             "status" => 0
    //         ]);
    //     }

    //     $url = 'https://maps.googleapis.com/maps/api/staticmap?center=' . $input['pickup_lat'] . ',' . $input['pickup_lng'] . '&zoom=16&size=600x300&maptype=roadmap&markers=color:red%7Clabel:L%7C' . $input['pickup_lat'] . ',' . $input['pickup_lng'] . '&key=' . env('MAP_KEY');
    //     $img = 'trip_request_static_map/' . md5(time()) . '.png';
    //     file_put_contents('uploads/' . $img, file_get_contents($url));

    //     // if ($input['trip_type'] == 1) {
    //     //     $fares = $this->calculate_daily_fare($input['vehicle_type'], $input['km'], $input['promo'], $input['country_id']);
    //     // }

    //     // if ($input['trip_type'] == 2) {
    //     //     $fares = $this->calculate_rental_fare($input['vehicle_type'], $input['package_id'], $input['promo'], $input['country_id'], 0, 0);
    //     // }

    //     // if ($input['trip_type'] == 3) {
    //     //     $fares = $this->calculate_outstation_fare($input['vehicle_type'], $input['km'], $input['promo'], $input['country_id'], 1);
    //     // }
    //     $fares = $this->calculate_daily_fare(1, $distance, $input['promo'], $input['country_id']);

    //     $booking_request = $input;
    //     $booking_request['distance'] = $input['km'];
    //     unset($booking_request['km']);
    //     $booking_request['total'] = $fares['total_fare'];
    //     $booking_request['sub_total'] = $fares['fare'];
    //     $booking_request['discount'] = $fares['discount'];
    //     $booking_request['tax'] = $fares['tax'];
    //     $booking_request['static_map'] = $img;
    //     $booking_request['payment_method'] = $input['payment_method'];
    //     $id = TripRequest::create($booking_request)->id;

    //     if ($input['booking_type'] == 2) {
    //         return response()->json([
    //             "result" => $id,
    //             "booking_type" => $input['booking_type'],
    //             "message" => 'Success',
    //             "status" => 1
    //         ]);
    //     }

    //     $newPost = $database

    //     ->getReference('/triprequest/' . $id)
    //     ->update([
    //         'request_id' => $id,
    //         'driver_id' => $driver_selected[1],
    //         'pikup_lat' => $input['pickup_lat'],
    //         'pikup_lng' => $input['pickup_lng'],
    //     ]);


    //     $newPost = $database
    //         ->getReference('/customers/' . $input['customer_id'])
    //         ->update([
    //             'booking_id' => $id,
    //             'booking_status' => 1,

    //         ]);
    //     if ($input['trip_type'] == 2) {
    //         $input['drop_address'] = "Sorry, customer not mentioned";
    //     }

    //     $newPost = $database
    //         ->getReference('/vehicles/' . $input['vehicle_type'] . '/' . $min_driver_id)
    //         ->update([
    //             'booking_id' => $id,
    //             'booking_status' => 1,
    //             'pickup_address' => $input['pickup_address'],
    //             'drop_address' => $input['drop_address'],
    //             'total' => $fares['total_fare'],
    //             // 'customer_name' => Customer::where('id',$input['customer_id'])->value('first_name'),
    //             'customer_name' => Customer::where('id', $input['customer_id'])->value('full_name'),
    //             'static_map' => $img,
    //             'trip_type' => DB::table('trip_types')->where('id', $input['trip_type'])->value('name')
    //         ]);

    //     return response()->json([
    //         "result" => $id,
    //         "booking_type" => $input['booking_type'],
    //         "message" => 'Success',
    //         "status" => 1
    //     ]);
    // }
    public function ride_confirm(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'promo' => 'required',
            'pickup_address' => 'required',
            // 'pickup_date' => 'required',
            'pickup_lat' => 'required',
            'pickup_lng' => 'required',
            // 'drop_address' => 'required',
            // 'drop_lat' => 'required',
            // 'drop_lng' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $input['vehicle_type'] = 1;
        $input['customer_id'] = Auth::user()->id;
        $input['country_id'] = 1;
        $input['trip_type'] = 1;
        // $input['km'] = $this->distance($input['pickup_lat'], $input['pickup_lng'], $input['drop_lat'], $input['drop_lng'], 'K') ;
        $input['pickup_date'] = date("Y-m-d H:i:s");

        $current_date = $this->get_date($input['country_id']);
        $interval_time = $this->date_difference($input['pickup_date'], $current_date);

        if ($interval_time <= 30) {
            $input['booking_type'] = 1;
            $input['status'] = 1;
        } else {
            $input['booking_type'] = 2;
            $input['status'] = 2;
        }

        $factory = (new Factory())->withServiceAccount(config_path() . '/' . env('FIREBASE_FILE'))
            ->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();

        $drivers = $database->getReference('/drivers/')
            ->getSnapshot()->getValue();
        // dd($drivers);
        $min_distance = 0;
        $min_driver_id = 0;
        $booking_searching_radius = TripSetting::value('booking_searching_radius');
        foreach ($drivers as  $value) {
            $amount = Driver::where('id', $value['driver_id'])->value('wallet');
            if ($amount > (-1)) {
                $distance = $this->distance($input['pickup_lat'], $input['pickup_lng'], $value['lat'], $value['lng'], 'K');
                if ($value['online_status'] == 1 && $value['booking_status'] == 0) {
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

        if ($min_driver_id == 0) {
            return response()->json([
                "message" => 'نأسف لا يوجد سيارات متاحة الآن',
                "status" => 0
            ]);
        }

        // $url = 'https://maps.googleapis.com/maps/api/staticmap?center=' . $input['pickup_lat'] . ',' . $input['pickup_lng'] . '&zoom=16&size=600x300&maptype=roadmap&markers=color:red%7Clabel:L%7C' . $input['pickup_lat'] . ',' . $input['pickup_lng'] . '&key=' . env('MAP_KEY');
        // $img = 'trip_request_static_map/' . md5(time()) . '.png';
        // file_put_contents('uploads/' . $img, file_get_contents($url));

        // if ($input['trip_type'] == 1) {
        //     $fares = $this->calculate_daily_fare($input['vehicle_type'], $input['km'], $input['promo'], $input['country_id']);
        // }

        $booking_request = $input;
        // $booking_request['distance'] = $input['km'];
        unset($booking_request['km']);
        // $booking_request['total'] = $fares['total_fare'];
        // $booking_request['sub_total'] = $fares['fare'];
        // $booking_request['discount'] = $fares['discount'];
        $booking_request['tax'] = 0;
        $booking_request['status'] = 2;
        // $booking_request['static_map'] = $img;
        $booking_request['promo_code'] = $input['promo'];
        $customer = Customer::where('id', Auth::user()->id)->first();
        if ($input['payment_method'] == 2) {
            if ($customer->wallet < 1) {
                $this->send_fcm('لا يوجد رصيد كافي', 'عذراً لا يوجد في محفظتك رصيد كافي , سيكون الدفع كاش', $customer->fcm_token);
                $booking_request['payment_method'] = 1;
            } else {
                $booking_request['payment_method'] = 2;
            }
        } else {
            $booking_request['payment_method'] = $input['payment_method'];
        }
        $id = TripRequest::create($booking_request)->id;

        $newPost = $database
            ->getReference('/customers/' . $input['customer_id'])
            ->update([
                'booking_id' => $id,
                'booking_status' => 1
            ]);

        $newPost = $database
            ->getReference('/vehicles/' . $input['vehicle_type'] . '/' . $min_driver_id)
            ->update([
                'booking_id' => $id,
                'booking_status' => 1,
                'pickup_address' => $input['pickup_address'],
                // 'drop_address' => $input['drop_address'],
                // 'total' => $fares['total_fare'],
                'customer_name' => Customer::where('id', $input['customer_id'])->value('full_name'),
                // 'static_map' => $img,
                'trip_type' => DB::table('trip_types')->where('id', $input['trip_type'])->value('name')
            ]);

        $newPost = $database

            ->getReference('/triprequest/' . $id)
            ->update([
                'request_id' => $id,
                'driver_id' => $min_driver_id,
                'pikup_lat' => $input['pickup_lat'],
                'pikup_lng' => $input['pickup_lng'],
            ]);

        return response()->json([
            "result" => $id,
            "booking_type" => $input['booking_type'],
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function drivers()
    {
        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'))->withServiceAccount(config_path() . '/' . env('FIREBASE_FILE'));
        $database = $factory->createDatabase();

        $drivers = $database->getReference('/vehicles/' . 1)
            ->getSnapshot()->getValue();
        $driver_selected = [];
        $i = 1;

        foreach ($drivers as $key => $value) {
            // $test = $value['booking_status'];
            if ($value['booking_status'] == 0 && $value['online_status'] == 1) {
                $driver_selected[$i] = $value['driver_id'];
            }
            $i++;
        }
        return $driver_selected;
    }

    public function ride_later()
    {
        $data = TripRequest::select('id', 'country_id', 'pickup_date')->where('status', 2)->where('booking_type', 2)->orderBy('pickup_date')->get();

        $timeout_trip_time = 15;
        $future_trip_time = 15;

        foreach ($data as $key => $value) {
            $value->pickup_date = date("Y-m-d H:i:s", strtotime($value->pickup_date));
            $current_date = $this->get_date($value->country_id);

            $interval_time = $this->date_difference($value->pickup_date, $current_date);

            if ($value->pickup_date > $current_date) {
                if ($interval_time <= $future_trip_time) {
                    $this->find_driver($value->id);
                }
            } else {
                if ($interval_time <= $timeout_trip_time) {
                    $this->find_driver($value->id);
                } else {
                    TripRequest::where('id', $value->id)->update(['status' => 5]);
                }
            }
        }
    }

    public function find_driver($trip_request_id)
    {

        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();

        $trip_request = TripRequest::where('id', $trip_request_id)->first();


        $drivers = $database->getReference('/drivers/')->getSnapshot()->getValue();
        // dd($drivers);
        $rejected_drivers = DriverTripRequest::where('trip_request_id', $trip_request_id)->where('status', 0)->pluck('driver_id')->toArray();

        $min_distance = 0;
        $min_driver_id = 0;
        $booking_searching_radius = TripSetting::value('booking_searching_radius');

        foreach ($drivers as $key => $value) {
            if (!in_array($value['driver_id'], $rejected_drivers)) {
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
        if($min_driver_id == 0) {
            $min_driver_id = 99999999999;
        }
        // $old_driver_id = $database
        //         ->getReference('/triprequest/' . $trip_request->id)
        //         ->getSnapshot()->getValue();
        // dd($min_driver_id);
        // // $old_driver_id['driver_id']
        // if (in_array($min_driver_id, $rejected_drivers)) {
        //     $min_driver_id = 0;
        // }

        if ($min_driver_id == 0) {
            $newPost = $database
                ->getReference('/customers/' . $trip_request->customer_id)
                ->update([
                    'booking_id' => 0,
                    'booking_status' => 0
                ]);

            return 0;
        }

        // if ($trip_request->trip_type == 2) {
        //     $trip_request->drop_address = "Sorry, customer not mentioned";
        // }

        // $newPost = $database
        //     ->getReference('/vehicles/' . $trip_request->vehicle_type . '/' . $min_driver_id)
        //     ->update([
        //         'booking_id' => $trip_request->id,
        //         'booking_status' => 1,
        //         'pickup_address' => $trip_request->pickup_address,
        //         'drop_address' => $trip_request->drop_address,
        //         'total' => $trip_request->total,
        //         'static_map' => $trip_request->static_map,
        //         // 'customer_name' => Customer::where('id',$trip_request->customer_id)->value('first_name'),
        //         'customer_name' => Customer::where('id', $trip_request->customer_id)->value('full_name'),
        //         'trip_type' => DB::table('trip_types')->where('id', $trip_request->trip_type)->value('name')
        //     ]);

        $newPost = $database

            ->getReference('/triprequest/' . $trip_request->id)
            ->update([
                'driver_id' => $min_driver_id,
            ]);

        return $trip_request->id;
    }

    public function get_date($country_id)
    {
        $date = new DateTime();
        $usersTimezone = Country::where('id', $country_id)->value('timezone');

        if ($usersTimezone) {
            // Convert timezone
            $tz = new DateTimeZone($usersTimezone);
            $date->setTimeZone($tz);

            // Output date after
            return $date->format('Y-m-d H:i:s');
        } else {
            return $date->format('Y-m-d H:i:s');
        }
    }

    public function date_difference($date1, $date2)
    {
        $date1 = date_create($date1);
        $date2 = date_create($date2);
        $diff = date_diff($date1, $date2);
        $days = $diff->format("%a");
        $hours = $diff->format("%h");
        $min = $diff->format("%i");

        $minutes = $days * 24 * 60;
        $minutes += $hours * 60;
        $minutes += $min;
        return $minutes;
    }

    public function trip_reject(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'trip_id' => 'required',
            // 'driver_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $input['driver_id'] = Auth::user()->id;

        $trip = TripRequest::where('id', $input['trip_id'])->first();

        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();

        // dd($drivers_id);
        // $i = 1;
        // foreach ($drivers_id as $driver_del) {
        //     $n = "$i";
        //     // dd($n);

        //     if ($driver_del[$n]->toArray() == strval(Auth::user()->id) )
        //     {
        //         $driver_del[$i] = null;
        //     }
        //     $i = intval($i);
        //     $i++;
        // }
        // dd($drivers_id);


        /*$newPost = $database
        ->getReference('customers/'.$trip->customer_id)
        ->update([
            'booking_id' => 0,
            'booking_status' => 0
        ]);*/

        // remove($toBeDeleted);

        $newPost = $database
            ->getReference('/drivers/' . $input['driver_id'])
            ->update([
                'booking_status' => 0
            ]);

        // $newPost = $database
        //     ->getReference('/vehicles/' . $trip->vehicle_type . '/' . $input['driver_id'])
        //     ->update([
        //         'booking_id' => 0,
        //         'booking_status' => 0,
        //         'pickup_address' => "",
        //         'drop_address' => "",
        //         'total' => 0,
        //         'customer_name' => "",
        //         "static_map" => "",
        //         'trip_type' => ""
        //     ]);

        if ($trip->booking_type == 1) {
            TripRequest::where('id', $input['trip_id'])->update(['status' => 4]);
        }

        $data['driver_id'] = $input['driver_id'];
        $data['trip_request_id'] = $input['trip_id'];
        $data['status'] = 0;

        DriverTripRequest::create($data);
        $this->find_driver($input['trip_id']);
        return response()->json([
            "message" => 'Success',
            "status" => 1
        ]);
    }


    public function trip_accept(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'trip_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $driver_id = Auth::user()->id;
        $trip_requset = TripRequest::where('id', $input['trip_id'])->first();

        $data = [];
        $data['country_id'] = $trip_requset->country_id;
        $data['customer_id'] = $trip_requset->customer_id;
        $data['trip_type'] = $trip_requset->trip_type;
        $data['booking_type'] = $trip_requset->booking_type;
        $data['package_id'] = $trip_requset->package_id;
        $data['package_id'] = $trip_requset->package_id;
        $data['driver_id'] = $driver_id;
        $data['vehicle_id'] = DriverVehicle::where('driver_id', $driver_id)->value('id');
        $data['vehicle_type'] = $trip_requset->vehicle_type;
        $data['payment_method'] = $trip_requset->payment_method;
        $data['promo_code'] = $trip_requset->promo;
        $data['pickup_date'] = date('Y-m-d H:i:s');
        $data['pickup_address'] = $trip_requset->pickup_address;
        $data['pickup_lat'] = $trip_requset->pickup_lat;
        $data['pickup_lng'] = $trip_requset->pickup_lng;
        $data['drop_address'] = $trip_requset->drop_address;
        $data['drop_lat'] = $trip_requset->drop_lat;
        $data['drop_lng'] = $trip_requset->drop_lng;
        $data['total'] = $trip_requset->total;
        $data['sub_total'] = $trip_requset->sub_total;
        $data['tax'] = $trip_requset->tax;
        $data['discount'] = $trip_requset->discount;
        $data['status'] = 1;
        $trip = Trip::create($data);
        Trip::where('id', $trip->id)->update(['trip_id' => $trip->id]);

        if ($trip->promo_code) {
            $user_promo['customer_id'] = $trip->customer_id;
            $user_promo['promo_id'] = PromoCode::where('promo_code', $trip->promo_code)->value('id');
            $user_promo['trip_id'] = $trip->id;
            $user_promo = UserPromoHistory::create($user_promo);
            // dd($user_promo);
        }

        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'))->withServiceAccount(config_path() . '/' . env('FIREBASE_FILE'));
        $database = $factory->createDatabase();
        $customer = Customer::where('id', $trip->customer_id)->first();
        $driver = Driver::where('id', $trip->driver_id)->first();
        $vehicle = DriverVehicle::where('id', $trip->vehicle_id)->first();
        $current_status = BookingStatus::where('id', 1)->first();
        $new_status = BookingStatus::where('id', 2)->first();
        $payment_method = PaymentMethod::where('id', $trip->payment_method)->value('Payment');

        $data['driver_id'] = $trip->driver_id;
        $data['trip_request_id'] = $trip->id;
        $data['status'] = 1;
        DriverTripRequest::create($data);

        //Trip Firebase
        $data = [];
        $data['id'] = $trip->id;
        $data['trip_id'] = $trip->id;
        $data['trip_type'] = $trip->trip_type;
        $data['customer_id'] = $trip->customer_id;
        $data['customer_name'] = $customer->full_name;
        $data['customer_profile_picture'] = $customer->profile_picture;
        $data['customer_phone_number'] = $customer->phone_number;
        $data['driver_id'] = $trip->driver_id;
        $data['driver_name'] = $driver->full_name;
        $data['driver_profile_picture'] = $driver->profile_picture;
        $data['driver_phone_number'] = $driver->phone_number;
        $data['pickup_address'] = $trip->pickup_address;
        $data['pickup_lat'] = $trip->pickup_lat;
        $data['pickup_lng'] = $trip->pickup_lng;
        $data['drop_address'] = $trip->drop_address;
        $data['drop_lat'] = $trip->drop_lat;
        $data['drop_lng'] = $trip->drop_lng;
        $data['payment_method'] = $payment_method;
        $data['pickup_date'] = $trip->pickup_date;
        $data['total'] = $trip->total;
        $data['collection_amount'] = $trip->total;
        $data['vehicle_id'] = $trip->vehicle_id;
        $data['vehicle_image'] = $vehicle->vehicle_image;
        $data['vehicle_number'] = $vehicle->vehicle_number;
        $data['vehicle_color'] = $vehicle->color;
        $data['vehicle_name'] = $vehicle->vehicle_name;
        $data['customer_status_name'] = $current_status->customer_status_name;
        $data['driver_status_name'] = $current_status->status_name;
        $data['status'] = 1;
        $data['new_driver_status_name'] = $new_status->status_name;
        $data['new_status'] = 2;
        $data['driver_lat'] = 0;
        $data['driver_lng'] = 0;
        $data['bearing'] = 0;

        $newPost = $database
            ->getReference('/trips/' . $trip->id)
            ->update($data);

        $newPost = $database
            ->getReference('/customers/' . $trip['customer_id'])
            ->update([
                'booking_id' => $trip->id,
                'booking_status' => 2
            ]);

        $newPost = $database
            ->getReference('/drivers/' . $trip->driver_id)
            ->update([
                'booking_status' => 2
            ]);

        $newPost = $database
            ->getReference('/vehicles/' . $trip['vehicle_type'] . '/' . $trip->driver_id)
            ->update([
                'booking_id' => $trip->id,
                'booking_status' => 2
            ]);

        $newPost = $database
            ->getReference('/triprequest/' . $input['trip_id'])
            ->update([
                'driver_id' => 9999999999,
            ]);

        TripRequest::where('id', $input['trip_id'])->update(['status' => 3]);
        $image = "image/tripaccept.png";

        $trip_rate = new RateTrip;
        $trip_rate->trip_id = $trip->id;
        $trip_rate->customer_id = $trip->customer_id;
        $trip_rate->driver_id = $trip->driver_id;
        $trip_rate->customer_is_rate = 0;
        $trip_rate->driver_is_rate = 0;
        $trip_rate->save();
        $this->send_fcm($current_status->status_name, $current_status->customer_status_name, $customer->fcm_token);
        $this->save_notifcation($trip->customer_id, 1, $current_status->status_name, $current_status->customer_status_name, $image);

        return response()->json([
            "result" => $trip->id,
            "message" => 'Success',
            "status" => 1
        ]);
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

    public function trip_cancel_by_customer(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'trip_id' => 'required',
            'reason_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $input['status'] = 7;
        $data['trip_id'] = $input['trip_id'];
        $data['reason_id'] = $input['reason_id'];
        $data['cancelled_by'] = 1;

        TripCancellation::create($data);

        Trip::where('id', $input['trip_id'])->update(['status' => $input['status']]);

        $trip = Trip::where('id', $input['trip_id'])->first();

        //Firebase
        //$factory = (new Factory)->withServiceAccount(config_path().'/'.env('FIREBASE_FILE'));
        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();

        $newPost = $database
            ->getReference('/customers/' . $trip->customer_id)
            ->update([
                'booking_id' => 0,
                'booking_status' => 0
            ]);

        $newPost = $database
            ->getReference('/drivers/' . $trip->driver_id)
            ->update([
                'booking_status' => 0
            ]);

        $vehicle_type = DriverVehicle::where('id', $trip->vehicle_id)->value('vehicle_type');

        $newPost = $database
            ->getReference('/vehicles/' . $vehicle_type . '/' . $trip->driver_id)
            ->update([
                'booking_id' => 0,
                'booking_status' => 0,
                'customer_name' => '',
                'pickup_address' => '',
                'drop_address' => '',
                'total' => '',
                'trip_type' => ''
            ]);

        $newPost = $database
            ->getReference('/trips/' . $input['trip_id'])
            ->update([
                'status' => $input['status']
            ]);

        return response()->json([
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function request_cancel()
    {
        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();

        $triprequest = TripRequest::where('customer_id', Auth::user()->id)->get()->last();
        $image = "image/tripaccept.png";
        if ($triprequest->status == 2) {
            $triprequest->update(['status' => 6]);
            $customer = Customer::where('id', Auth::user()->id)->first();
            if ($customer->fcm_token) {
                $this->save_notifcation($customer->id, 1, 'إلغاء الطلب', 'تم إلغاء طلب الرحلة, ننتظرك في طلب آخر', $image);
                $this->send_fcm('إلغاء الطلب', 'تم إلغاء طلب الرحلة, ننتظرك في طلب آخر', $customer->fcm_token);
            }
            $newPost = $database
                ->getReference('/triprequest/' . $triprequest->id)
                ->remove();

            return response()->json([
                "message" => 'تم إلغاء الطلب بنجاح',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'لا يوجد طلبات حالياً أو تم قبول طلبك',
                "status" => 0
            ]);
        }
    }

    public function get_fare(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'trip_type' => 'required',
            'km' => 'required',
            'vehicle_type' => 'required',
            'promo' => 'required',
            'country_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $data = [];
        $vehicle = DB::table('daily_fare_management')->where('id', 1)->first();

        if (is_object($vehicle)) {
            $data['base_fare'] = number_format((float)$vehicle->base_fare, 2, '.', '');
            $data['km'] = $input['km'];
            $data['price_per_km'] = number_format((float)$vehicle->price_per_km, 2, '.', '');
            $additional_fare = number_format($data['km']) * $data['price_per_km'];
            $data['additional_fare'] = number_format((float)$additional_fare, 2, '.', '');
            $fare =  $data['base_fare'] + $data['additional_fare'];
            $data['fare'] = number_format((float)$fare, 2, '.', '');
            $data['total_fare'] = number_format((float)$fare, 2, '.', '');
        }
        if ($input['promo'] == 0) {
            $data['discount'] = 0.00;
        } else {
            $data['discount'] = 0.00;
            $promo = DB::table('promo_codes')->where('promo_code', $input['promo'])->first();
            if ($promo->promo_type == 5) {
                $total_fare = $data['total_fare'] - $promo->discount;
                if ($total_fare > 0) {
                    $data['discount'] = number_format((float)$promo->discount, 2, '.', '');
                    $data['total_fare'] = number_format((float)$total_fare, 2, '.', '');
                } else {
                    $data['discount'] = number_format((float)$data['total_fare'], 2, '.', '');
                    $data['total_fare'] = 0.00;
                }
            } else {
                $discount = ($promo->discount / 100) * $data['total_fare'];
                $total_fare = $data['total_fare'] - $discount;
                $data['discount'] = number_format((float)$discount, 2, '.', '');
                $data['total_fare'] = number_format((float)$total_fare, 2, '.', '');
            }
        }
        return response()->json([
            "result" => $data,
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function calculate_daily_fare($vehicle_type, $km, $promo, $country_id)
    {
        $data = [];
        $vehicle = DB::table('daily_fare_management')->where('id', $vehicle_type)->first();

        if (is_object($vehicle)) {
            $data['base_fare'] = number_format((float)$vehicle->base_fare, 2, '.', '');
            $data['km'] = $km;
            $data['price_per_km'] = number_format((float)$vehicle->price_per_km, 2, '.', '');
            $additional_fare = number_format($data['km']) * $data['price_per_km'];
            $data['additional_fare'] = number_format((float)$additional_fare, 2, '.', '');
            $fare =  $data['base_fare'] + $data['additional_fare'];
            $data['fare'] = number_format((float)$fare, 2, '.', '');
            $data['total_fare'] = number_format((float)$fare, 2, '.', '');
        }

        if ($promo == 0) {
            $data['discount'] = 0.00;
        } else {
            $data['discount'] = 0.00;
            $promo = PromoCode::where('promo_code', $promo)->first();
            if ($promo->promo_type == 2) {
                $total_fare = $data['total_fare'] - $promo->discount;
                if ($total_fare > 0) {
                    $data['discount'] = number_format((float)$promo->discount, 2, '.', '');
                    $data['total_fare'] = number_format((float)$total_fare, 2, '.', '');
                } else {
                    $data['discount'] = number_format((float)$data['total_fare'], 2, '.', '');
                    $data['total_fare'] = 0.00;
                }
            } else {
                $discount = ($promo->discount / 100) * $data['total_fare'];
                $total_fare = $data['total_fare'] - $discount;
                $data['discount'] = number_format((float)$discount, 2, '.', '');
                $data['total_fare'] = number_format((float)$total_fare, 2, '.', '');
            }
        }
        return $data;
    }

    public function calculate_rental_fare($vehicle_type, $package_id, $promo, $country_id, $extra_km, $extra_hour)
    {
        $data = [];
        $package_price = DB::table('rental_fare_management')->where('package_id', $package_id)->first();

        if (is_object($package_price)) {
            $data['price_per_km'] = number_format((float)$package_price->price_per_km, 2, '.', '');
            $data['price_per_hour'] = number_format((float)$package_price->price_per_hour, 2, '.', '');
            $data['base_fare'] = number_format((float)$package_price->package_price, 2, '.', '');

            $additional_km_fare = $extra_km * $data['price_per_km'];
            $data['additional_km_fare'] = number_format((float)$additional_km_fare, 2, '.', '');
            $additional_hour_fare = $extra_hour * $data['price_per_hour'];
            $data['additional_hour_fare'] = number_format((float)$additional_hour_fare, 2, '.', '');

            $data['price_per_hour'] = number_format((float)$package_price->price_per_hour, 2, '.', '');
            $fare = $data['additional_km_fare'] + $data['additional_hour_fare'] + $data['base_fare'];
            $data['fare'] = number_format((float)$fare, 2, '.', '');

            //Tax
            $taxes = DB::table('tax_lists')->where('id', $country_id)->get();
            $total_tax = 0.00;
            if (count($taxes)) {
                foreach ($taxes as $key => $value) {
                    $total_tax = $total_tax + ($value->percent / 100) * $data['fare'];
                }
            }
            $data['tax'] = number_format((float)$total_tax, 2, '.', '');
            $total_fare = $data['tax'] + $data['fare'];
            $data['total_fare'] = number_format((float)$total_fare, 2, '.', '');
        }

        if ($promo == 0) {
            $data['discount'] = 0.00;
        } else {
            $data['discount'] = 0.00;
            $promo = DB::table('promo_codes')->where('id', $promo)->first();
            if ($promo->promo_type == 5) {
                $total_fare = $data['total_fare'] - $promo->discount;
                if ($total_fare > 0) {
                    $data['discount'] = number_format((float)$promo->discount, 2, '.', '');
                    $data['total_fare'] = number_format((float)$total_fare, 2, '.', '');
                } else {
                    $data['discount'] = number_format((float)$data['total_fare'], 2, '.', '');
                    $data['total_fare'] = 0.00;
                }
            } else {
                $discount = ($promo->discount / 100) * $data['total_fare'];
                $total_fare = $data['total_fare'] - $discount;
                $data['discount'] = number_format((float)$discount, 2, '.', '');
                $data['total_fare'] = number_format((float)$total_fare, 2, '.', '');
            }
        }

        return $data;
    }

    public function calculate_outstation_fare($vehicle_type, $km, $promo, $country_id, $days)
    {

        $data = [];
        $vehicle = DB::table('outstation_fare_management')->where('id', $vehicle_type)->first();

        if (is_object($vehicle)) {
            $data['base_fare'] = number_format((float)$vehicle->base_fare, 2, '.', '');
            $data['km'] = $km;
            $data['price_per_km'] = number_format((float)$vehicle->price_per_km, 2, '.', '');
            $data['driver_allowance'] = number_format((float)$vehicle->driver_allowance, 2, '.', '');
            $data['driver_allowance'] = $data['driver_allowance'] * $days;
            $additional_fare = number_format($data['km']) * $data['price_per_km'];
            $additional_fare = $additional_fare * 2;
            $data['additional_fare'] = number_format((float)$additional_fare, 2, '.', '');
            $fare =  $data['base_fare'] + $data['additional_fare'] + $data['driver_allowance'];
            $data['fare'] = number_format((float)$fare, 2, '.', '');

            //Tax
            $taxes = DB::table('tax_lists')->where('id', $country_id)->get();
            $total_tax = 0.00;
            if (count($taxes)) {
                foreach ($taxes as $key => $value) {
                    $total_tax = $total_tax + ($value->percent / 100) * $data['fare'];
                }
            }
            $data['tax'] = number_format((float)$total_tax, 2, '.', '');
            $total_fare = $data['tax'] + $data['fare'];
            $data['total_fare'] = number_format((float)$total_fare, 2, '.', '');
        }

        if ($promo == 0) {
            $data['discount'] = 0.00;
        } else {
            $data['discount'] = 0.00;
            $promo = DB::table('promo_codes')->where('id', $promo)->first();
            if ($promo->promo_type == 5) {
                $total_fare = $data['total_fare'] - $promo->discount;
                if ($total_fare > 0) {
                    $data['discount'] = number_format((float)$promo->discount, 2, '.', '');
                    $data['total_fare'] = number_format((float)$total_fare, 2, '.', '');
                } else {
                    $data['discount'] = number_format((float)$data['total_fare'], 2, '.', '');
                    $data['total_fare'] = 0.00;
                }
            } else {
                $discount = ($promo->discount / 100) * $data['total_fare'];
                $total_fare = $data['total_fare'] - $discount;
                $data['discount'] = number_format((float)$discount, 2, '.', '');
                $data['total_fare'] = number_format((float)$total_fare, 2, '.', '');
            }
        }

        return $data;
    }

    public function fare()
    {
        $data = DB::table('daily_fare_management')->select('base_fare', 'price_per_km', 'price_time')->where('id', 1)->first();
        return response()->json([
            "result" => $data,
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function customer_bookings(Request $request)
    {
        // $input = $request->all();
        // $validator = Validator::make($input, [
        //     'customer_id' => 'required'
        // ]);
        // if ($validator->fails()) {
        //     return $this->sendError($validator->errors());
        // }
        $id = Auth::user()->id;
        // dd($id);
        $data = DB::table('trips')
            ->leftJoin('customers', 'customers.id', 'trips.customer_id')
            ->leftJoin('drivers', 'drivers.id', 'trips.driver_id')
            ->leftJoin('payment_methods', 'payment_methods.id', 'trips.payment_method')
            ->leftJoin('trip_types', 'trip_types.id', 'trips.trip_type')
            ->leftJoin('driver_vehicles', 'driver_vehicles.id', 'trips.vehicle_id')
            ->leftJoin('vehicle_categories', 'vehicle_categories.id', 'driver_vehicles.vehicle_type')
            ->leftJoin('booking_statuses', 'booking_statuses.id', 'trips.status')
            // ->select('trips.*','customers.first_name as customer_name','drivers.first_name as driver_name','drivers.profile_picture','payment_methods.payment','driver_vehicles.brand','driver_vehicles.color','driver_vehicles.vehicle_name','driver_vehicles.vehicle_number','trip_types.name as trip_type','booking_statuses.status_name','vehicle_categories.vehicle_type')
            ->select('trips.*', 'customers.full_name as customer_name', 'drivers.full_name as driver_name', 'drivers.profile_picture', 'payment_methods.payment', 'driver_vehicles.brand', 'driver_vehicles.color', 'driver_vehicles.vehicle_name', 'driver_vehicles.vehicle_number', 'trip_types.name as trip_type', 'booking_statuses.status_name', 'vehicle_categories.vehicle_type')
            ->where('trips.customer_id', $id)->orderBy('id', 'DESC')
            ->get();
        foreach ($data as $trip) {
            $datetime1 = new DateTime($trip->end_time);
            $datetime2 = new DateTime($trip->start_time);
            if ($trip->status == 6) {
                $interval = $datetime1->diff($datetime2);
                $trip->intereval = $interval->format('%H:%I');
            }
            $trip->end_time = $datetime1->format('Y-m-d H:i');
            $trip->start_time = $datetime2->format('Y-m-d H:i');
            $trip->ratings = RateTrip::where('trip_id', $trip->id)->value('driver_rate');
        }
        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function driver_bookings(Request $request)
    {
        $data = DB::table('trips')
            ->leftJoin('customers', 'customers.id', 'trips.customer_id')
            ->leftJoin('drivers', 'drivers.id', 'trips.driver_id')
            ->leftJoin('payment_methods', 'payment_methods.id', 'trips.payment_method')
            ->leftJoin('driver_vehicles', 'driver_vehicles.id', 'trips.vehicle_id')
            ->leftJoin('vehicle_categories', 'vehicle_categories.id', 'driver_vehicles.vehicle_type')
            ->leftJoin('booking_statuses', 'booking_statuses.id', 'trips.status')
            // ->select('trips.*','customers.first_name as customer_name','drivers.first_name as driver_name','customers.profile_picture','payment_methods.payment','driver_vehicles.brand','driver_vehicles.color','driver_vehicles.vehicle_name','driver_vehicles.vehicle_number','booking_statuses.status_name','vehicle_categories.vehicle_type')
            ->select('trips.*', 'customers.full_name as customer_name', 'drivers.full_name as driver_name', 'customers.profile_picture', 'payment_methods.payment', 'driver_vehicles.brand', 'driver_vehicles.color', 'driver_vehicles.vehicle_name', 'driver_vehicles.vehicle_number', 'booking_statuses.status_name', 'vehicle_categories.vehicle_type')
            ->where('trips.driver_id', Auth::user()->id)->orderBy('id', 'DESC')
            ->get();
        // dd($data);
        foreach ($data as $trip) {
            if ($trip->status == 6) {
                $end = strtotime($trip->end_time);
                $start = strtotime($trip->start_time);
                $totalSecondsDiff = abs($end - $start);
                $trip->intereval = ($totalSecondsDiff / 60);
            }
        }
        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function change_statuses(Request $request)
    {
        // dd($request);
        $input = $request->all();
        $validator = Validator::make($input, [
            'trip_id' => 'required',
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $trip = Trip::where('id', $input['trip_id'])->first();
        if ($trip->status > 6) {
            $input['status'] = $trip->status;
        }

        Trip::where('id', $input['trip_id'])->update(['status' => $input['status']]);
        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();

        if ($input['status'] == 4) {
            Trip::where('id', $input['trip_id'])->update(['start_time' => date('Y-m-d H:i:s'), 'actual_pickup_address' => $input['address'], 'actual_pickup_lat' => $input['lat'], 'actual_pickup_lng' => $input['lng']]);
        }

        if ($input['status'] == 5) {
            $distance = $input['distance'];
            // dd($distance);
            Trip::where('id', $input['trip_id'])->update(['end_time' => date('Y-m-d H:i:s'), 'actual_drop_address' => $input['address'], 'actual_drop_lat' => $input['lat'], 'actual_drop_lng' => $input['lng']]);
            // $distance = $this->get_distance($input['trip_id']);
            Trip::where('id', $input['trip_id'])->update(['distance' => $distance]);
            $vehicle = DailyFareManagement::where('id', 1)->first();
            $trip = Trip::where('id', $input['trip_id'])->first();

            $end = strtotime($trip->end_time);
            $start = strtotime($trip->start_time);
            $totalSecondsDiff = abs($end - $start);
            $time = ($totalSecondsDiff / 60);
            if (is_null(Trip::where('id', $input['trip_id'])->value('time_minutes'))) {
                Trip::where('id', $input['trip_id'])->update(['time_minutes' => $time]);
            }
            $minutes = Trip::where('id', $input['trip_id'])->value('time_minutes');
            $price_time = $minutes * $vehicle->price_time;
            $price_time = number_format((float)$price_time, 2, '.', '');
            $price_distance = $distance * $vehicle->price_per_km;
            $fare = $vehicle->base_fare + $price_distance + $price_time;
            $fare = number_format((float)$fare, 2, '.', '');
            Trip::where('id', $input['trip_id'])->update(['sub_total' => $fare]);

            if ($trip->promo_code == 0) {
                Trip::where('id', $input['trip_id'])->update(['total' => $fare]);
            } else {
                $promo = PromoCode::where('promo_code', $trip->promo_code)->first();
                if ($promo->promo_type == 2) {
                    $total_fare = $fare - $promo->discount;
                    $promo->discount = number_format((float)$promo->discount, 2, '.', '');
                    $total_fare = number_format((float)$total_fare, 2, '.', '');
                    Trip::where('id', $input['trip_id'])->update(['total' => $total_fare, 'discount' => $promo->discount]);
                } elseif ($promo->promo_type == 1) {
                    $discount = ($promo->discount / 100) * $fare;
                    $total_fare = $fare - $discount;
                    $discount = number_format((float)$discount, 2, '.', '');
                    $total_fare = number_format((float)$total_fare, 2, '.', '');
                    Trip::where('id', $input['trip_id'])->update(['total' => $total_fare, 'discount' => $discount]);
                }
            }

            $newPost = $database
                ->getReference('/customers/' . $trip->customer_id)
                ->update([
                    'booking_id' => 0,
                    'booking_status' => 0
                ]);

            $newPost = $database
                ->getReference('/drivers/' . $trip->driver_id)
                ->update([
                    'booking_status' => 0
                ]);

            $vehicle_type = DriverVehicle::where('id', $trip->vehicle_id)->value('vehicle_type');
            $newPost = $database
                ->getReference('/vehicles/' . $vehicle_type . '/' . $trip->driver_id)
                ->update([
                    'booking_id' => 0,
                    'booking_status' => 0,
                    'pickup_address' => "",
                    'customer_name' => "",
                    'drop_address' => "",
                    'total' => ""
                ]);
            $this->calculate_earnings($input['trip_id']);
            $this->reward_point($input['trip_id']);
        }

        if ($input['status'] == 8) {
            $driver = Driver::where('id', $trip->driver_id)->first();
            $cancellation_settings = CancellationSetting::where('id', 1)->first();
            if ($driver->count_cancel > $cancellation_settings->no_of_free_cancellation) {
                $wallet = $driver->wallet - $cancellation_settings->cancellation_charge;
                $count_cancel = $driver->count_cancel + 1;
                Driver::where('id', $trip->driver_id)->update(['wallet' => $wallet, 'count_cancel' => $count_cancel]);
            } else {
                $count_cancel = $driver->count_cancel + 1;
                Driver::where('id', $trip->driver_id)->update(['count_cancel' => $count_cancel]);
            }
        }

        if ($input['status'] != 6) {
            $current_status = BookingStatus::where('id', $input['status'])->first();
            $new_status = BookingStatus::where('id', $input['status'])->first();
        } elseif ($input['status'] = 6) {
            $distance = Trip::where('id', $input['trip_id'])->sum('distance');
            $trip_customer = Trip::where('id', $input['trip_id'])->value('customer_id');
            $customer = Customer::find($trip_customer);
            $customer->points += $distance;
            $customer->save();
        }
        $customer_id = Trip::where('id', $input['trip_id'])->value('customer_id');
        $fcm_token = Customer::where('id', $customer_id)->value('fcm_token');
        $image = "image/tripaccept.png";

        if ($fcm_token) {
            $current_status = BookingStatus::where('id', $input['status'])->first();
            $new_status = BookingStatus::where('id', $input['status'])->first();
            $this->save_notifcation($customer_id, 1, $current_status->status_name, $current_status->customer_status_name, $image);
            $this->send_fcm($current_status->status_name, $current_status->customer_status_name, $fcm_token);
        }

        $newPost = $database
            ->getReference('/trips/' . $input['trip_id'])
            ->update([
                'customer_status_name' => $current_status->customer_status_name,
                'status' => $current_status->id,
                'driver_status_name' => $current_status->status_name,
                'new_status' => $new_status->id,
                'new_driver_status_name' => $new_status->status_name
            ]);

        if ($input['status'] == 5) {
            $data_trip = Trip::where('id', $input['trip_id'])->first();
            $interval = (strtotime($data_trip->end_time) - strtotime($data_trip->start_time)) / 60;
            $data_trip1 = [];
            $data_trip1['time'] = $interval;
            $data_trip1['distance'] = $data_trip->distance;
            if ($data_trip->payment_method == 1) {
                $data_trip1['payment_method'] = "cash";
            } else {
                $data_trip1['payment_method'] = "wallet";
            }
            return response()->json([
                "data" => $data_trip1,
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Success',
                "status" => 1
            ]);
        }
    }

    public function recive_mony(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'amount' => 'required',
            'trip_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $this->change_statuses($request);
        return response()->json([
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function reward_point($trip_id)
    {
        $trip = Trip::select('customer_id', 'distance')->where('id', $trip_id)->get()->last();
        if (round($trip->distance) != 0) {
            $point = new Point;
            $point->customer_id = $trip->customer_id;
            $point->trip_id = $trip_id;
            $point->type = 1;
            $point->point = round($trip->distance);
            $distance = number_format((float)$trip->distance, 2, '.', '');
            $point->details = "قطعت مسافة بمقدار " . $distance;
            $point->icon = "rewards/taxi.png";
            $point->save();
            return response()->json([
                "trip" => $point,
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'لم تقطع الحد الأدنى من المسافة',
                "status" => 1
            ]);
        }
    }

    public function point(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $point = Customer::where('id', $input['customer_id'])->value('points');
        $value = DB::table('app_settings')->value('points');
        $point_back = $point % $value;
        $point_to_wallet = $point - $point_back;
        $to_wallet = $point_to_wallet / $value;
        return response()->json([
            "point_to_wallet" => $point_to_wallet,
            "to_wallet" => $to_wallet,
            "message" => 'Success',
            "status" => 1
        ]);
    }


    public function point_to_wallet(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $value = DB::table('app_settings')->value('points');
        $point = Customer::where('id', $input['customer_id'])->value('points');
        $point_back = $point % $value;
        $input['point'] = $point - $point_back;
        if ($point >= $input['point']) {
            $to_wallet = $input['point'] / $value;
            $return_point = $input['point'] % $value;
            $new_point = $point - $input['point'];
            $to_wallet = intval($to_wallet);
            $wallet = Customer::where('id', $input['customer_id'])->value('wallet');
            $new_wallet = $wallet + $to_wallet;
            Customer::where('id', $input['customer_id'])->update(['points' => $new_point, 'wallet' => $new_wallet]);

            $point = new Point;
            $point->customer_id = $input['customer_id'];
            $point->trip_id = 0;
            $point->type = 2;
            $point->point = $input['point'] - $return_point;
            $point->details = 'تحويل نقاط إلى رصيد';
            $point->icon = "rewards/taxi.png";
            $point->save();

            CustomerWalletHistory::create(['country_id' => 1, 'customer_id' => $input['customer_id'], 'type' => 2, 'message' => 'أضافة رصيد من تحويل النقاط', 'amount' => $to_wallet, 'transaction_type' => 1]);

            return response()->json([
                "trip" => $point,
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'لا يوجد نقاط كفاية',
                "status" => 1
            ]);
        }
    }

    public function get_reward()
    {
        $total_point = Customer::where('id', Auth::user()->id)->value('points');
        $reward = Point::where('customer_id', Auth::user()->id)->orderBy('id', 'desc')->get();;
        return response()->json([
            "total_point" => $total_point,
            "result" => $reward,
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function get_invoice(Request $request)
    {
        $invoice = CustomerWalletHistory::where('customer_id', Auth::user()->id)->orderBy('id', 'desc')->get();
        $wallet = Customer::where('id', Auth::user()->id)->value('wallet');
        return response()->json([
            "wallet" => $wallet,
            "result" => $invoice,
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function get_statuses()
    {
        $id = Auth::user()->id;
        $trip_request = TripRequest::select('status')->where('customer_id', $id)->get()->last();
        $price_km = DailyFareManagement::where('id', 1)->first();
        if (is_null($trip_request)) {
            return response()->json([
                "price" => $price_km,
                "message" => 'Not have any request trip',
                "status" => 1
            ]);
        } elseif ($trip_request->status == 3) {
            $trip = Trip::select('id', 'trip_id', 'customer_id', 'driver_id', 'vehicle_id', 'status', 'pickup_address', 'drop_address', 'actual_drop_address', 'payment_method', 'start_time', 'end_time', 'distance', 'total')->where('customer_id', $id)->get()->last();
            $trip['interval'] = (strtotime($trip->end_time) - strtotime($trip->start_time)) / 60;
            $trip['interval'] = number_format((float)$trip['interval'], 2, '.', '');
            $driver = Driver::select('full_name', 'profile_picture', 'overall_ratings', 'phone_with_code')->where('id', $trip->driver_id)->get()->last();
            $vehicle = DriverVehicle::where('driver_id', $trip->driver_id)->get(['vehicle_image', 'brand', 'vehicle_name', 'vehicle_number'])->last();
            $trip_rate = RateTrip::where('trip_id', $trip->id)->get()->last();

            // $trip_rate = DB::table('rate_trips')->where('trip_id', $trip->id)->get()->last();
            // dd($trip);
            if ($trip->status == 2) {
                $driver['duration'] = $this->distanc_driver($trip->id);
            }
            if ($trip->status == 6) {
                if (!isset($trip_rate->customer_is_rate) || $trip_rate->customer_is_rate == 0) {
                    $trip->status = 5;
                } else {
                    return response()->json([
                        "price" => $price_km,
                        "message" => 'Not have any request trip',
                        "status" => 1
                    ]);
                }
            }

            if ($trip->status < 6) {
                if ($trip_rate->customer_is_rate == 1) {
                    $trip->status = 6;
                }
                $driver['overall_ratings'] = number_format((float)$driver['overall_ratings'], 1, '.', '');
                return response()->json([
                    "result" => $trip,
                    "driver" => $driver,
                    "vehicle" => $vehicle,
                    "price" => $price_km,
                    "message" => 'Success',
                    "status" => 1
                ]);
            } else {
                return response()->json([
                    "price" => $price_km,
                    "message" => 'Not have any request trip',
                    "status" => 1
                ]);
            }
        } elseif ($trip_request->status == 2) {
            return response()->json([
                "price" => $price_km,
                "message" => 'Looking for a driver',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "price" => $price_km,
                "message" => 'Not have any request trip',
                "status" => 1
            ]);
        }
    }

    public function calculate_fare($trip_id)
    {
        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();
        $trip = Trip::where('id', $trip_id)->first();
        $distance = $this->get_distance($trip_id);
        if ($distance != 0 && is_object($trip)) {
            $this->calculate_daily_fare($trip->vehicle_type, $distance, $trip->promo_code, $trip->country_id);
        }
    }

    public function update_payment_mode($trip_id, $trip, $fare)
    {
        $customer_wallet = Customer::where('id', $trip->customer_id)->value('wallet');
        if ($customer_wallet && $customer_wallet > 0) {
            $remaining_fare = $customer_wallet - $fare;
            $remaining_fare = number_format((float)$remaining_fare, 2, '.', '');
            if ($remaining_fare >= 0) {
                Trip::where('id', $trip_id)->update(['payment_method' => PaymentMethod::where('country_id', $trip->country_id)->where('payment_type', 2)->value('id')]);
                Customer::where('id', $trip->customer_id)->update(['wallet' => $remaining_fare]);
                $payment_history['trip_id'] = $trip_id;
                $payment_history['mode'] = "Wallet";
                $payment_history['amount'] = $fare;
                PaymentHistory::create($payment_history);
                CustomerWalletHistory::create(['country_id' => $trip->country_id, 'customer_id' => $trip->customer_id, 'type' => 2, 'message' => 'Amount debited for booking(#' . $trip->trip_id . ')', 'amount' => $fare, 'transaction_type' => 3]);
                return 0;
            } else {
                Trip::where('id', $trip_id)->update(['payment_method' => PaymentMethod::where('country_id', $trip->country_id)->where('payment_type', 3)->value('id')]);
                Customer::where('id', $trip->customer_id)->update(['wallet' => 0]);
                $payment_history['trip_id'] = $trip_id;
                $payment_history['mode'] = "Wallet";
                $payment_history['amount'] = $customer_wallet;
                PaymentHistory::create($payment_history);
                $payment_history['trip_id'] = $trip_id;
                $payment_history['mode'] = "Cash";
                $payment_history['amount'] = abs($remaining_fare);
                PaymentHistory::create($payment_history);
                CustomerWalletHistory::create(['country_id' => $trip->country_id, 'customer_id' => $trip->customer_id, 'type' => 2, 'message' => 'Amount debited for booking(#' . $trip->trip_id . ')', 'amount' => $customer_wallet, 'transaction_type' => 3]);
                return abs($remaining_fare);
            }
        } else {
            $payment_history['trip_id'] = $trip_id;
            $payment_history['mode'] = "Cash";
            $payment_history['amount'] = $fare;
            PaymentHistory::create($payment_history);
            return abs($fare);
        }
    }

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

    public function calculate_earnings($trip_id)
    {
        $trip = Trip::where('id', $trip_id)->first();
        $payment_method = PaymentMethod::where('id', $trip->payment_method)->first();
        $admin_commission_percent = TripSetting::value('admin_commission');
        $total = number_format((float)$trip->total, 2, '.', '');
        $admin_commission = ($admin_commission_percent / 100) * $total;
        $admin_commission = number_format((float)$admin_commission, 2, '.', '');
        $driver_earning = $total - $admin_commission;
        $driver_earning = number_format((float)$driver_earning, 2, '.', '');
        DriverEarning::create(['trip_id' => $trip_id, 'driver_id' => $trip->driver_id, 'amount' => $driver_earning]);
        $old_wallet = Driver::where('id', $trip->driver_id)->value('wallet');

        if ($payment_method->payment_type == 2) {
            $old_wallet_customer = Customer::where('id', $trip->customer_id)->value('wallet');
            $new_wallet_customer = $old_wallet_customer - $total;
            Customer::where('id', $trip->customer_id)->update(['wallet' => $new_wallet_customer]);
            CustomerWalletHistory::create(['country_id' => $trip->country_id, 'customer_id' => $trip->customer_id, 'type' => 1, 'message' => 'طلب رحلة و الدفع من المحفظة', 'amount' => $total, 'transaction_type' => $payment_method->payment_type]);
            $new_wallet = $old_wallet + $driver_earning;
            Driver::where('id', $trip->driver_id)->update(['wallet' => $new_wallet]);
            DriverWalletHistory::create(['driver_id' => $trip->driver_id, 'type' => 1, 'transaction_type' => 2, 'message' => 'تم إضافة رصيد لمحفظتك لرحلة رقم' . $trip->trip_id, 'amount' => $driver_earning]);
        } else if ($payment_method->payment_type == 1) {
            $new_wallet = $old_wallet - $admin_commission;
            Driver::where('id', $trip->driver_id)->update(['wallet' => $new_wallet]);
            CustomerWalletHistory::create(['country_id' => $trip->country_id, 'customer_id' => $trip->customer_id, 'type' => 1, 'message' => 'طلب رحلة والدفع كاش ', 'amount' => $total, 'transaction_type' => $payment_method->payment_type]);
            DriverWalletHistory::create(['driver_id' => $trip->driver_id, 'type' => 2, 'transaction_type' => 2, 'message' => 'تم خصم رصيد من محفظتك لرحلة ' . $trip->trip_id, 'amount' => $admin_commission]);
        }
    }

    public function direct_booking(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'phone_number' => 'required',
            'customer_name' => 'required',
            'pickup_address' => 'required',
            'pickup_lat' => 'required',
            'pickup_lng' => 'required',
            'drop_address' => 'required',
            'drop_lat' => 'required',
            'drop_lng' => 'required',
            'driver_id' => 'required',
            'km' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $driver = Driver::where('id', $input['driver_id'])->first();
        $vehicle = DriverVehicle::where('driver_id', $input['driver_id'])->first();
        $customer = Customer::where('phone_number', $input['phone_number'])->first();
        if (!is_object($customer)) {

            $country = Country::where('id', $driver->country_id)->first();
            $currency = Currency::where('country_id', $country->id)->first();

            // $customer['first_name'] = $input['customer_name'];
            $customer['full_name'] = $input['customer_name'];
            $customer['country_id'] = $country->id;
            $customer['country_code'] = $country->phone_code;
            $customer['currency'] = $currency->currency;
            $customer['currency_short_code'] = $currency->currency_short_code;
            $customer['phone_number'] = $input['phone_number'];
            $customer['phone_with_code'] = $country->phone_code . $input['phone_number'];
            $customer['status'] = 1;
            Customer::create($customer);
            $customer = Customer::where('phone_number', $input['phone_number'])->first();
        }

        $data['km'] = $input['km'];
        $data['vehicle_type'] = $vehicle->vehicle_type;
        $data['customer_id'] = $customer->id;
        $data['booking_type'] = 2;
        $data['promo'] = 0;
        $data['country_id'] = $customer->country_id;
        $data['payment_method'] = PaymentMethod::where('country_id', $customer->country_id)->where('payment_type', 1)->value('id');
        $data['pickup_address'] = $input['pickup_address'];
        $data['pickup_lat'] = $input['pickup_lat'];
        $data['pickup_lng'] = $input['pickup_lng'];
        $data['drop_address'] = $input['drop_address'];
        $data['drop_lat'] = $input['drop_lat'];
        $data['drop_lng'] = $input['drop_lng'];

        $url = 'https://maps.googleapis.com/maps/api/staticmap?center=' . $input['pickup_lat'] . ',' . $input['pickup_lng'] . '&zoom=16&size=600x300&maptype=roadmap&markers=color:red%7Clabel:L%7C' . $input['pickup_lat'] . ',' . $input['pickup_lng'] . '&key=' . env('MAP_KEY');
        $img = 'trip_request_static_map/' . md5(time()) . '.png';
        file_put_contents('uploads/' . $img, file_get_contents($url));

        $fares = $this->calculate_daily_fare($data['vehicle_type'], $data['km'], $data['promo'], $data['country_id']);

        $booking_request = $data;
        $booking_request['distance'] = $data['km'];
        unset($booking_request['km']);
        $booking_request['total'] = $fares['total_fare'];
        $booking_request['sub_total'] = $fares['fare'];
        $booking_request['discount'] = $fares['discount'];
        $booking_request['tax'] = $fares['tax'];
        $booking_request['trip_type'] = 1;
        $booking_request['booking_type'] = 1;
        $booking_request['package_id'] = 0;
        $booking_request['static_map'] = $img;
        $booking_request['pickup_date'] = date("Y-m-d H:i:s");
        $id = TripRequest::create($booking_request)->id;
        //print_r($id);exit;

        //$factory = (new Factory)->withServiceAccount(config_path().'/'.env('FIREBASE_FILE'));
        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();

        $newPost = $database
            ->getReference('/customers/' . $data['customer_id'])
            ->update([
                'booking_id' => $id,
                'booking_status' => 1
            ]);

        $newPost = $database
            ->getReference('/drivers/' . $input['driver_id'])
            ->update([
                'booking_status' => 1
            ]);

        $newPost = $database
            ->getReference('/vehicles/' . $data['vehicle_type'] . '/' . $input['driver_id'])
            ->update([
                'booking_id' => $id,
                'booking_status' => 1,
                'pickup_address' => $data['pickup_address'],
                // 'customer_name' => $customer->first_name,
                'customer_name' => $customer->full_name,
                'drop_address' => $data['drop_address'],
                'total' => $booking_request['total'],
                'static_map' => $booking_request['static_map'],
                'trip_type' => DB::table('trip_types')->where('id', $booking_request['trip_type'])->value('name')
            ]);

        //$this->auto_trip_accept($id,$input['driver_id']);
        return response()->json([
            "result" => $id,
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function auto_trip_accept($trip_id, $driver_id)
    {
        $input['trip_id'] = $trip_id;
        $input['driver_id'] = $driver_id;

        $trip = TripRequest::where('id', $input['trip_id'])->first()->toArray();
        $customer_id = TripRequest::where('id', $input['trip_id'])->value('customer_id');
        $phone_with_code = Customer::where('id', $customer_id)->value('phone_with_code');

        $data = $trip;
        $data['driver_id'] = $input['driver_id'];
        $data['promo_code'] = $data['promo'];
        unset($data['promo']);
        unset($data['id']);
        $data['pickup_date'] = $trip['pickup_date'];
        $data['country_id'] = $trip['country_id'];
        $data['package_id'] = $trip['package_id'];
        $data['trip_type'] = $trip['trip_type'];
        $data['status'] = 1;
        $data['vehicle_type'] = $trip['vehicle_type'];
        $data['vehicle_id'] = DB::table('driver_vehicles')->where('driver_id', $input['driver_id'])->value('id');
        $data['otp'] = $otp = rand(1000, 9999);
        $phone = '+' . $phone_with_code;
        $message = "Hi " . env('APP_NAME') . " , Your OTP code is:  " . $data['otp'];
        $this->sendSms($phone, $message);
        $id = Trip::create($data)->id;

        if ($data['promo_code']) {
            $user_promo['user_id'] = $data['customer_id'];
            $user_promo['promo_id'] = $data['promo_code'];
            $user_promo['status'] = 1;
            UserPromoHistory::create($user_promo);
        }

        $trip_id = str_pad($id, 6, "0", STR_PAD_LEFT);
        Trip::where('id', $id)->update(['trip_id' => $trip_id]);


        //Firebase
        //$factory = (new Factory)->withServiceAccount(config_path().'/'.env('FIREBASE_FILE'));
        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();



        $trip_details = Trip::where('id', $id)->first();
        $customer = Customer::where('id', $trip_details->customer_id)->first();
        $driver = Driver::where('id', $trip_details->driver_id)->first();
        $vehicle = DriverVehicle::where('id', $trip_details->vehicle_id)->first();
        $current_status = BookingStatus::where('id', 1)->first();
        $new_status = BookingStatus::where('id', 2)->first();
        $payment_method = PaymentMethod::where('id', $trip_details->payment_method)->value('Payment');

        $data['driver_id'] = $input['driver_id'];
        $data['trip_request_id'] = $input['trip_id'];
        $data['status'] = 3;

        DriverTripRequest::create($data);

        //Trip Firebase
        $data = [];
        $data['id'] = $id;
        $data['trip_id'] = $trip_id;
        $data['trip_type'] = $trip_details->trip_type;
        $data['customer_id'] = $trip_details->customer_id;
        // $data['customer_name'] = $customer->first_name;
        $data['customer_name'] = $customer->full_name;
        $data['customer_profile_picture'] = $customer->profile_picture;
        $data['customer_phone_number'] = $customer->phone_number;
        $data['driver_id'] = $trip_details->driver_id;
        // $data['driver_name'] = $driver->first_name;
        $data['driver_name'] = $driver->full_name;
        $data['driver_profile_picture'] = $driver->profile_picture;
        $data['driver_phone_number'] = $driver->phone_number;
        $data['pickup_address'] = $trip_details->pickup_address;
        $data['pickup_lat'] = $trip_details->pickup_lat;
        $data['pickup_lng'] = $trip_details->pickup_lng;
        $data['drop_address'] = $trip_details->drop_address;
        $data['drop_lat'] = $trip_details->drop_lat;
        $data['drop_lng'] = $trip_details->drop_lng;
        $data['payment_method'] = $payment_method;
        $data['pickup_date'] = $trip_details->pickup_date;
        $data['total'] = $trip_details->total;
        $data['collection_amount'] = $trip_details->total;
        $data['vehicle_id'] = $trip_details->vehicle_id;
        $data['otp'] = $trip_details->otp;
        $data['vehicle_image'] = $vehicle->vehicle_image;
        $data['vehicle_number'] = $vehicle->vehicle_number;
        $data['vehicle_color'] = $vehicle->color;
        $data['vehicle_name'] = $vehicle->vehicle_name;
        $data['customer_status_name'] = $current_status->customer_status_name;
        $data['driver_status_name'] = $current_status->status_name;
        $data['status'] = 1;
        $data['new_driver_status_name'] = $new_status->status_name;
        $data['new_status'] = 2;
        $data['driver_lat'] = 0;
        $data['driver_lng'] = 0;
        $data['bearing'] = 0;

        $newPost = $database
            ->getReference('/trips/' . $trip_details->id)
            ->update($data);

        $newPost = $database
            ->getReference('/customers/' . $trip['customer_id'])
            ->update([
                'booking_id' => $id,
                'booking_status' => 2
            ]);

        $newPost = $database
            ->getReference('/drivers/' . $input['driver_id'])
            ->update([
                'booking_status' => 2
            ]);

        $newPost = $database
            ->getReference('/vehicles/' . $trip['vehicle_type'] . '/' . $input['driver_id'])
            ->update([
                'booking_id' => $id,
                'booking_status' => 2
            ]);

        TripRequest::where('id', $input['trip_id'])->update(['status' => 3]);
        $image = "image/tripaccept.png";
        $this->send_fcm($current_status->status_name, $current_status->customer_status_name, $customer->fcm_token);
        $this->save_notifcation($trip_details->customer_id, 1, $current_status->status_name, $current_status->customer_status_name, $image);

        return response()->json([
            "result" => $id,
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function create_reward($trip_id)
    {
        $trip = Trip::where('id', $trip_id)->first();
        $scratch_settings = ScratchCardSetting::first();
        if ($scratch_settings->coupon_type == 2) {
            $rand = rand(10, 100);
            if ($rand % 2 == 0) {
                $data['customer_id'] = $trip->customer_id;
                $data['title'] = "Better Luck Next Time !";
                $data['description'] = "Take Advantage of this Amazing offer before. Now it's too late. Better Luck next time";
                $data['view_status'] = 0;
                $data['image'] = "rewards/better_luck.png";
                $data['type'] = 0;
                $data['ref_id'] = 0;
                $data['status'] = 1;
                CustomerOffer::create($data);
                return true;
            }
        }
        $lucky_offer_limit = $scratch_settings->lucky_offer;
        $last_lucky_offer = CustomerOffer::where('type', 2)->orderBy('id', 'DESC')->value('id');
        $last_offer = CustomerOffer::orderBy('id', 'DESC')->value('id');
        $lucky_find = $last_offer - $last_lucky_offer;

        if ($lucky_find >= $lucky_offer_limit) {
            $lucky_status = 1;
        } else {
            $lucky_status = 0;
        }

        if ($lucky_status == 1) {
            $lucky_count = LuckyOffer::count();
            $lucky_id = rand(1, $lucky_count);
            $lucky = LuckyOffer::where('id', $lucky_id)->first();
            $data['customer_id'] = $trip->customer_id;
            $data['title'] = $lucky->offer_name;
            $data['description'] = $lucky->offer_description;
            $data['image'] = $lucky->image;
            $data['ref_id'] = $lucky_id;
            $data['view_status'] = 0;
            $data['type'] = 2;
            $data['status'] = 1;
            CustomerOffer::create($data);
        } else {
            $instant_count = InstantOffer::count();
            $instant_id = rand(1, $instant_count);
            $instant = InstantOffer::where('id', $instant_id)->first();
            $data['customer_id'] = $trip->customer_id;
            // $data['title'] = $instant->offer_name;
            // $data['description'] = $instant->offer_description;
            $data['view_status'] = 0;
            // $data['ref_id'] = $instant_id;
            $data['image'] = "rewards/reward_image.png";
            $data['type'] = 1;
            $data['status'] = 1;
            CustomerOffer::create($data);
        }
        return true;
    }

    public function spot_booking_otp(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'phone_number' => 'required',
            'customer_name' => 'required',
            'pickup_address' => 'required',
            'pickup_lat' => 'required',
            'pickup_lng' => 'required',
            'drop_address' => 'required',
            'drop_lat' => 'required',
            'drop_lng' => 'required',
            'driver_id' => 'required',
            'km' => 'required',
            'fare' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $driver = Driver::where('id', $input['driver_id'])->first();
        $vehicle = DriverVehicle::where('driver_id', $input['driver_id'])->first();
        $country = Country::where('id', $driver->country_id)->first();
        $phone_with_code = $country->phone_code . $input['phone_number'];
        $currency = Currency::where('country_id', $country->id)->value('currency');
        //print_r($currency);exit;

        $data['km'] = $input['km'];
        $data['vehicle_type'] = $vehicle->vehicle_type;
        $data['booking_type'] = 2;
        $data['promo'] = 0;
        $data['country_id'] = $driver->country_id;

        //$data['fare'] = [];
        $fares = $this->calculate_daily_fare($data['vehicle_type'], $data['km'], $data['promo'], $data['country_id']);
        $data['otp'] = rand(1000, 9999);
        $message = "Hi " . env('APP_NAME') . " , Your OTP code is:  " . $data['otp'] . ". Pickup location:  " . $input['pickup_address'] . ", Drop location:" . $input['drop_address'] . ". Total fare:" . $currency . $input['fare'];
        $this->sendSms($phone_with_code, $message);
        return response()->json([
            "result" => $data,
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function cancel_test()
    {
        $timeNow = time();
        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();
        $triprequests = TripRequest::where('status', 2)->get();
        $image = "image/tripaccept.png";
        foreach ($triprequests as $triprequest) {
            $timeInterval = ($timeNow - strtotime($triprequest->created_at)) / 60;

            if ($timeInterval > 1) {
                $triprequest->update(['status' => 4]);
                $customer = Customer::where('id', $triprequest->customer_id)->first();
                if ($customer->fcm_token) {
                    $current_status = TripRequestStatus::where('id', 5)->first();
                    // dd($current_status);
                    $new_status = TripRequestStatus::where('id', 5)->first();
                    $this->save_notifcation($customer->id, 1, 'لم يتم العثور على سائق', 'لا يتوفر حالياً سائقين', $image);
                    $this->send_fcm('لم يتم العثور على سائق', 'لا يتوفر حالياً سائقين', $customer->fcm_token);
                }

                $newPost = $database
                    ->getReference('/triprequest/' . $triprequest->id)
                    ->remove();
            }
        }
    }

    public function send_invoice(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required',
            'country_code' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $booking_details = Trip::where('id', $input['id'])->first();
        $customer = Customer::where('id', $booking_details->customer_id)->first();
        $email = $customer->email;
        $app_setting = AppSetting::first();
        $data = array();
        $data['logo'] = $app_setting->logo;
        $data['booking_id'] = $booking_details->trip_id;
        $data['customer_name'] = Customer::where('id', $booking_details->customer_id)->value('first_name');
        $data['pickup_address'] = $booking_details->pickup_address;
        $data['drop_address'] = $booking_details->drop_address;
        $data['start_time'] = $booking_details->start_time;
        $data['end_time'] = $booking_details->end_time;
        $data['driver'] = (Driver::where('id', $booking_details->driver_id)->value('first_name') != '') ? Driver::where('id', $booking_details->driver_id)->value('first_name') : "---";
        $country = Country::where('phone_code', $input['country_code'])->value('id');
        $data['country_id'] = $country;
        $data['currency'] = Currency::where('country_id', $data['country_id'])->value('currency');
        $data['payment_method'] = PaymentMethod::where('id', $booking_details->payment_method)->value('payment');
        $data['sub_total'] = $booking_details->sub_total;
        $data['discount'] =  $booking_details->discount;
        $data['total'] =  $booking_details->total;
        $data['tax'] =  $booking_details->tax;
        $data['status'] =  Status::where('id', $booking_details->status)->value('name');
        $mail_header = array("data" => $data);
        $this->send_order_mail($mail_header, 'Enjoy the ride', $email);
        return response()->json([
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function driver_invoice(Request $request)
    {
        // $input = $request->all();
        // $validator = Validator::make($input, [
        //     'trip_id' => 'required',
        // ]);
        // if ($validator->fails()) {
        //     return $this->sendError($validator->errors());
        // }

        $trip = Trip::where('driver_id', Auth::user()->id)->get()->last();

        // $fare = number_format((float)$base_far + ($price_per_km * $distance) + ($price_time * $interval));

        if (isset($trip)) {
            $app_setting = AppSetting::first();
            $data = array();

            $trip->start_time;
            $trip->end_time;

            $vehicle = DB::table('daily_fare_management')->where('id', 1)->first();
            $base_fare = number_format((float)$vehicle->base_fare, 2, '.', '');
            $distance = $this->get_distance($trip->trip_id);
            $price_per_km = number_format((float)$vehicle->price_per_km, 2, '.', '');
            $price_time = number_format((float)$vehicle->price_time, 2, '.', '');
            $interval = (strtotime($trip->end_time) - strtotime($trip->start_time)) / 60;
            $data['sub_total'] = $price_per_km * $distance;
            $data['waiting_time'] = $price_time * $interval;
            $data['base_fare'] = $base_fare;
            $data['discount'] =  $trip->discount;
            $data['total'] =  $trip->total;
            return response()->json([
                "invoice" => $data,
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'لا توجد رحلات',
                "status" => 1
            ]);
        }

    }

    public function detailes_invoice(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'trip_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $trip = Trip::where('id', $input['trip_id'])->get()->first();


        $trip->start_time;
        $trip->end_time;

        $vehicle = DB::table('daily_fare_management')->where('id', 1)->first();
        $base_fare = number_format((float)$vehicle->base_fare, 2, '.', '');
        $distance = $trip->distance;
        $price_per_km = number_format((float)$vehicle->price_per_km, 2, '.', '');
        $price_time = number_format((float)$vehicle->price_time, 2, '.', '');
        $interval = (strtotime($trip->end_time) - strtotime($trip->start_time)) / 60;
        // $fare = number_format((float)$base_far + ($price_per_km * $distance) + ($price_time * $interval));

        $data['sub_total'] = $price_per_km * $distance;
        $data['waiting_time'] = $price_time * $interval;
        $data['base_fare'] = $base_fare;
        $data['discount'] =  $trip->discount;
        $data['total'] =  $trip->total;
        return response()->json([
            "invoice" => $data,
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function get_distance($trip_id)
    {
        $trip = Trip::where('id', $trip_id)->first();
        $url = 'https://maps.googleapis.com/maps/api/directions/json?origin=' . $trip->actual_pickup_lat . ',' . $trip->actual_pickup_lng . '&destination=' . $trip->actual_drop_lat . ',' . $trip->actual_drop_lng . '&key=' . env('MAP_KEY');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $err = curl_error($ch);  //if you need
        curl_close($ch);
        $result = json_decode($response);
        if (@$result->routes[0]->legs[0]->distance->text) {

            $distance = str_replace(" km", "", $result->routes[0]->legs[0]->distance->text);
            $distance = str_replace(" m", "", $distance);
            return $distance;
        } else {
            return 0;
        }
    }

    public function customer_distance(Request $request)
    {
        $input['customer_id'] = Auth::user()->id;
        $data = Trip::where('trips.customer_id', $input['customer_id'])->sum('distance');
        $wallet = Customer::where('customers.id', $input['customer_id'])->value('wallet');
        $name = Customer::where('customers.id', $input['customer_id'])->value('full_name');
        $phone = Customer::where('customers.id', $input['customer_id'])->value('phone_with_code');
        $point = Customer::where('customers.id', $input['customer_id'])->value('points');
        return response()->json([
            "full_name" => $name,
            "phone" => $phone,
            "distance" => $data,
            "point" => $point,
            "wallet" => $wallet,
            "message" => 'Success',
            "status" => 1
        ]);
    }

    //calculate actual distance between Driver and customer pickup addres
    public function distanc_driver($trip_id)
    {
        // $customer = Customer::where('id', Auth::user()->id)->first();
        $trip = Trip::where('id', $trip_id)->get()->last();

        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();
        $reference = $database
            ->getReference('/drivers/' . $trip->driver_id);

        $driver = $reference->getValue();
        // $url = 'https://maps.googleapis.com/maps/api/directions/json?origin=' . $trip->actual_pickup_lat . ',' . $trip->actual_pickup_lng . '&destination=' . $driver['lat'] . ',' . $driver['lng'] . '&key=' . env('MAP_KEY');
        $url = 'https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=' . $trip->pickup_lat . ',' . $trip->pickup_lng . '&destinations=' . $driver['lat'] . ',' . $driver['lng'] . '&key=' . env('MAP_KEY');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $err = curl_error($ch);  //if you need
        curl_close($ch);
        $result = json_decode($response);

        if (@$result->rows[0]->elements[0]->duration->value) {
            return $result->rows[0]->elements[0]->duration->value;
        } else {
            return 0;
        }
    }

    public function sendError($message)
    {
        $message = $message->all();
        $response['error'] = "validation_error";
        $response['message'] = implode('', $message);
        $response['status'] = "0";
        return response()->json($response, 200);
    }

    public function test()
    {
        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();
        $drivers = $database->getReference('/drivers/')
            ->getSnapshot()->getValue();

        foreach($drivers as $driver) {
            if($driver['online_status'] == 1) {
                $vehicle = DriverVehicle::where('driver_id', $driver['driver_id'])->first();
                if($driver['lat'] == $vehicle->lat && $driver['lng'] == $vehicle->lng) {
                    $newPost = $database->getReference('/drivers/' . $driver['driver_id'])
                            ->update([
                                'online_status' => 0,
                            ]);
                } else {
                    DriverVehicle::where('driver_id', $driver['driver_id'])->update([
                        'lat' => $driver['lat'],
                        'lng' => $driver['lng']
                    ]);
                }
            }
        }
    }

    public function request()
    {
        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();
        $value = $database->getReference('/triprequest/')
            ->getValue();
        // dd($value);
        foreach ($value as $triprequest) {
            $this->change_driver($triprequest['request_id'], $triprequest['driver_id']);
        }
    }


    public function change_driver($trip_id, $driver_id)
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
        $data['status'] = 4;

        DriverTripRequest::create($data);
        $this->find_driver($trip_id);
        return response()->json([
            "message" => 'Success',
            "status" => 1
        ]);
    }
}
