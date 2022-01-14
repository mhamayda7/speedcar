<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;
use App\Driver;
use App\Models\DriverQuery;
use App\DriverEarning;
use App\Customer;
use App\DriverBankKycDetail;
use App\Country;
use App\Currency;
use App\Trip;
use App\DriverTutorial;
use App\DriverVehicle;
use App\DriverWithdrawal;
use App\DriverWalletHistory;
use App\VehicleCategory;
use App\CustomerWalletHistory;
use App\Models\DailyFareManagement;
use App\Models\Point;
use App\Models\RateTrip;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;
use Illuminate\Support\Str;



class DriverController extends Controller
{
    /*public function check_phone(Request $request){

         $input = $request->all();
        $validator = Validator::make($input, [
        	'phone_with_code' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $data = array();
        $driver = Driver::where('phone_with_code',$input['phone_with_code'])->first();

        if(is_object($driver)){
            $data['is_available'] = 1;
            $data['otp'] = "";
            return response()->json([
                "result" => $data,
                "message" => 'Success',
                "status" => 1
            ]);
        }else{
            $data['is_available'] = 0;
            $data['otp'] = rand(1000,9999);
            $message = "Hi".env('APP_NAME')." , Your OTP code is:".$data['otp'];
            $this->sendSms($input['phone_with_code'],$message);
            return response()->json([
                "result" => $data,
                "message" => 'Success',
                "status" => 1
            ]);
        }

    }*/

    public function check_phone(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'phone_with_code' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $data = array();
        $driver = Driver::where('phone_with_code', $input['phone_with_code'])->first();

        if (is_object($driver)) {
            return response()->json([
                //"result" => 'Phone number already exist',
                "message" => 'Success',
                "status" => 1
            ]);
        } else {

            $data['otp'] = rand(1000, 9999);
            $message = "Hi" . env('APP_NAME') . " , Your OTP code is:" . $data['otp'];
            $this->sendSms($input['phone_with_code'], $message);
            return response()->json([
                "message" => 'Sorry this number not available please contact admin',
                "status" => 0
            ]);
        }
    }
    public function register(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'full_name' => 'required',
            'country_code' => 'required',
            'phone_number' => 'required|numeric|digits_between:9,20|unique:drivers,phone_number',
            'email' => 'required|email|regex:/^[a-zA-Z]{1}/|unique:drivers,email',
            'password' => 'required',
            'gender' => 'required',
            'date_of_birth' => 'required',
            'licence_number' => 'required',
            'fcm_token' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $options = [
            'cost' => 12,
        ];
        $input['password'] = password_hash($input["password"], PASSWORD_DEFAULT, $options);
        $input['status'] = 1;
        $data = Country::where('phone_code', $input['country_code'])->value('id');
        $input['country_id'] = $data;
        $input['currency'] = Currency::where('country_id', $input['country_id'])->value('currency');

        $phone_array = str_split($input['phone_number']);
        if ($phone_array[0] == 0) {
            $phone_without = substr($input['phone_number'], 1);
        } else {
            $phone_without = $input['phone_number'];
        }

        $input['phone_with_code'] = $input['country_code'] . $phone_without;
        $input['vehicle_model'] = "ادخال موديل المركبة";
        $input['vehicle_type'] = "ادخال نوع المركبة";
        $input['daily'] = 1;
        $input['rental'] = 0;
        $input['outstation'] = 0;
        $input['online_status'] = 0;
        $input['overall_ratings'] = 5;
        $input['no_of_ratings'] = 1;

        if ($request->hasFile('profile_picture')) {
            $image = $request->file('profile_picture');
            $imageName =  time() . '_' . $image->getClientOriginalName();
            $request->profile_picture->move(public_path('/uploads/drivers'), $imageName);
            $input['profile_picture'] = 'drivers/' . $imageName;
        }

        if ($request->hasFile('id_proof')) {
            $image = $request->file('id_proof');
            $proofImage =  time() . '_' . $image->getClientOriginalName();
            $request->id_proof->move(public_path('/uploads/image'), $proofImage);
            $input['id_proof'] = 'image/' . $proofImage;
        }

        if ($request->hasFile('vehicle_image')) {
            $image = $request->file('vehicle_image');
            $vehicle_image =  time() . '_' . $image->getClientOriginalName();
            $request->vehicle_image->move(public_path('/uploads/captain_vehicle_image'), $vehicle_image);
            $input['vehicle_image'] = 'captain_vehicle_image/' . $vehicle_image;
        }

        if ($request->hasFile('vehicle_licence')) {
            $image = $request->file('vehicle_licence');
            $vehicle_licence = time() . '_' . $image->getClientOriginalName();
            $request->vehicle_licence->move(public_path('/uploads/captain_vehicle_licence'), $vehicle_licence);
            $input['vehicle_licence'] = 'captain_vehicle_licence/' . $vehicle_licence;
        }

        $otp = rand(1000, 9999);
        $input['otp'] = $otp;
        $phone = '+' . $input['phone_with_code'];
        $message = "Hi " . env('APP_NAME') . "  , Your OTP code is:" . $otp;
        $this->smsSe($phone, $message);

        $driver = Driver::create($input);

        $unique = false;
        // Store tested results in array to not test them again
        $tested = [];
        do {
            // Generate random string of characters
            $random = Str::random(5);
            // Check if it's already testing
            // If so, don't query the database again
            if (in_array($random, $tested)) {
                continue;
            }
            // Check if it is unique in the database
            $count = DB::table('drivers')->where('referral_code', '=', $random)->count();
            // Store the random character in the tested array
            // To keep track which ones are already tested
            $tested[] = $random;
            // String appears to be unique
            if ($count == 0) {
                // Set unique to true to break the loop
                $unique = true;
            }
            // If unique is still false at this point
            // it will just repeat all the steps until
            // it has generated a random string of characters
        } while (!$unique);

        $driver->referral_code = $random;
        Driver::where('id', $driver->id)->update(['referral_code' => $driver->referral_code]);

        $token = $driver->createToken('name')->plainTextToken;

        $factory = (new Factory)->withServiceAccount(config_path() . '/' . env('FIREBASE_FILE'))
            ->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();

        $newPost = $database->getReference('/drivers/' . $driver->id)
        ->set([
            'driver_id' => $driver->id,
            'driver_name' => $input['full_name'],
            'status' => $input['status'],
            'lat' => 0,
            'lng' => 0,
            'online_status' => 0,
            'booking_status' => 0,
            'accuracy' => 0,
            'heading' => 0,
            'distance' => 0,
            'startlng' => 0,
            'startlat' => 0,
        ]);

        if (is_object($driver)) {
            return response()->json([
                "result" => $driver,
                "OTP" => $otp,
                "token" => $token,
                "message" => 'Registered Successfully',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong !',
                "status" => 0
            ]);
        }
    }

    public function login(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'phone_with_code' => 'required',
            'password' => 'required',
            'fcm_token' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $credentials = request(['phone_with_code', 'password']);
        $driver = Driver::where('phone_with_code', $credentials['phone_with_code'])->first();
        //$driver_phone = DriverVehicle::where('phone_with_code',$credentials['phone_with_code'])->value();

        if (!($driver)) {
            return response()->json([
                "message" => 'Invalid phone number or password',
                "status" => 0
            ]);
        }

        if (Hash::check($credentials['password'], $driver->password)) {

            if ($driver->status == 1) {
                $vehicle = DB::table('driver_vehicles')->where('driver_id', $driver->id)->first();
                // dd($vehicle);
                if (is_object($vehicle)) {
                    Driver::where('id', $driver->id)->update(['fcm_token' => $input['fcm_token']]);
                    $token = $driver->createToken('name')->plainTextToken;
                    return response()->json([
                        "result" => $driver,
                        "token" => $token,
                        "message" => 'Success',
                        "status" => 1
                    ]);
                } else {
                    return response()->json([
                        "message" => 'Your vehicle details not updated',
                        "status" => 0
                    ]);
                }
            } else {
                return response()->json([
                    "message" => 'Your account has been blocked',
                    "status" => 0
                ]);
            }
        } else {
            return response()->json([
                "message" => 'Invalid phone number or password',
                "status" => 0
            ]);
        }
    }

    public function signout(Request $request)
    {
        Driver::where('id', Auth::user()->id)->update(['fcm_token' => null]);
        $request->user()->currentAccessToken()->delete();
        Auth::user()->tokens->each(function($token, $key) {
            $token->delete();
        });
        return response()->json([
            "message" => 'Success Sign Out',
            "status" => 1
        ]);
    }

    public function profile_picture(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'driver_id' => 'required',
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif,svg'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        if ($request->hasFile('profile_picture')) {
            $image = $request->file('profile_picture');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/uploads/drivers');
            $image->move($destinationPath, $name);
            if (Driver::where('id', $input['driver_id'])->update(['profile_picture' => 'drivers/' . $name])) {
                return response()->json([
                    // "result" => Driver::select('id', 'first_name', 'last_name', 'phone_with_code','email','profile_picture','password','status')->where('id',$input['driver_id'])->first(),
                    "result" => Driver::select('id', 'full_name', 'phone_with_code', 'email', 'profile_picture', 'password', 'status')->where('id', $input['driver_id'])->first(),
                    "message" => 'Success',
                    "status" => 1
                ]);
            } else {
                return response()->json([
                    "message" => 'Sorry something went wrong...',
                    "status" => 0
                ]);
            }
        }
    }

    public function profile()
    {

        $result = Driver::where('id', Auth::user()->id)->select('id', 'full_name', 'phone_with_code', 'wallet', 'referral_code', 'overall_ratings')->first();
        $booking_complete = Trip::where('driver_id', Auth::user()->id)->where('status', 6)->count();

        if (is_object($result)) {
            $result['overall_ratings'] = number_format((float)$result['overall_ratings'], 2, '.', '');;
            return response()->json([
                "result" => $result,
                "booking_complete" => $booking_complete,
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong...',
                "status" => 0
            ]);
        }
    }

    public function profile_info()
    {
        $result = Driver::select('id', 'full_name', 'phone_with_code', 'gender', 'email', 'address', 'date_of_birth', 'status', 'referral_code', 'online_status')->where('id', Auth::user()->id)->first();
        if (is_object($result)) {
            if ($result->gender == 0) {
                $result->gender_name = "Update your gender";
            } else if ($result->gender == 1) {
                $result->gender_name = "Male";
            } else if ($result->gender == 2) {
                $result->gender_name = "Female";
            }
            return response()->json([
                "result" => $result,
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong...',
                "status" => 0
            ]);
        }
    }

    public function profile_update(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'driver_id' => 'required'

        ]);
        $input['id'] = $input['driver_id'];
        unset($input['driver_id']);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        if ($request->password) {
            $options = [
                'cost' => 12,
            ];
            $input['password'] = password_hash($input["password"], PASSWORD_DEFAULT, $options);
            $input['status'] = 1;
        } else {
            unset($input['password']);
        }

        if (Driver::where('id', $input['id'])->update($input)) {
            return response()->json([
                // "result" => Driver::select('id', 'first_name', 'last_name', 'phone_with_code','email','profile_picture','password','daily','rental','outstation','status')->where('id',$input['id'])->first(),
                "result" => Driver::select('id', 'full_name', 'phone_with_code', 'email', 'profile_picture', 'password', 'daily', 'rental', 'outstation', 'status')->where('id', $input['id'])->first(),
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong...',
                "status" => 0
            ]);
        }
    }

    public function driver_earning(Request $request)
    {

        // $input = $request->all();
        // $validator = Validator::make($input, [
        //     'id' => 'required'
        // ]);
        // if ($validator->fails()) {
        //     return $this->sendError($validator->errors());
        // }

        /*$data['total_earnings'] = DriverEarning::where('driver_id',$input['id'])->get()->sum("amount");
        $data['today_earnings'] = DriverEarning::where('driver_id',$input['id'])->whereDay('created_at', now()->day)->sum("amount");
        $data['earnings'] = DriverEarning::where('driver_id',$input['id'])->get();*/

        $total_earnings = DriverEarning::where('driver_id', Auth::user()->id)->get()->sum("amount");
        $data['total_earnings'] = number_format((float)$total_earnings, 2, '.', '');
        $today_earnings = DriverEarning::where('driver_id', Auth::user()->id)->whereDay('created_at', now()->day)->sum("amount");
        $data['today_earnings'] = number_format((float)$today_earnings, 2, '.', '');
        $data['earnings'] = DriverEarning::where('driver_id', Auth::user()->id)->get();


        if ($data) {
            return response()->json([
                "result" => $data,
                "count" => count($data),
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Something went wrong',
                "status" => 0
            ]);
        }
    }

    public function get_invoice(Request $request)
    {
        $invoice = DriverWalletHistory::where('driver_id', Auth::user()->id)->orderBy('id', 'desc')->get()->all();
        $wallet = Driver::where('id', Auth::user()->id)->value('wallet');
        return response()->json([
            "wallet" => $wallet,
            "result" => $invoice,
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function driver_wallet(Request $request)
    {

        // $input = $request->all();
        // $validator = Validator::make($input, [
        //     'id' => 'required'
        // ]);
        // if ($validator->fails()) {
        //     return $this->sendError($validator->errors());
        // }

        $data['wallet_amount'] = Driver::where('id', Auth::user()->id)->value('wallet');

        $data['wallets'] = DriverWalletHistory::where('driver_id', Auth::user()->id)->get();

        if ($data) {
            return response()->json([
                "result" => $data,
                "count" => count($data),
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Something went wrong',
                "status" => 0
            ]);
        }
    }
    public function driver_withdrawal_request(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'driver_id' => 'required',
            'amount' => 'required'

        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $input['status'] = 11;
        $vendor = DriverWithdrawal::create($input);
        Driver::where('id', $input['driver_id'])->update(['wallet' => 0]);
        return response()->json([
            "message" => 'success',
            "status" => 1
        ]);
    }

    public function driver_withdrawal_history(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $data['wallet_amount'] = Driver::where('id', $input['id'])->value('wallet');

        $data['withdraw'] =  DB::table('driver_withdrawals')
            ->leftjoin('statuses', 'statuses.id', '=', 'driver_withdrawals.status')
            ->select('driver_withdrawals.*', 'statuses.name')
            ->get();

        if ($data) {
            return response()->json([
                "result" => $data,
                "count" => count($data),
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Something went wrong',
                "status" => 0
            ]);
        }
    }

    public function get_bank_kyc_details(Request $request)
    {
        $input = $request->all();
        $kyc_details = DriverBankKycDetail::where('driver_id', $input['driver_id'])->first();
        if (is_object($kyc_details)) {
            return response()->json([
                "result" => $kyc_details,
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Still not updated',
                "status" => 0
            ]);
        }
    }

    public function bank_kyc_update(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'driver_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $is_details_exist = DriverBankKycDetail::where('driver_id', $input['driver_id'])->first();
        if (is_object($is_details_exist)) {
            $update = DriverBankKycDetail::where('driver_id', $input['driver_id'])->update($input);
        } else {
            $update =  DriverBankKycDetail::create($input);
        }
        if ($update) {
            return response()->json([
                "result" => DriverBankKycDetail::select('id', 'driver_id', 'bank_name', 'bank_account_number', 'ifsc_code', 'aadhar_number', 'pan_number')->where('driver_id', $input['driver_id'])->first(),
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong...',
                "status" => 0
            ]);
        }
    }

    public function change_online_status(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'online_status' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $input['id'] = Auth::user()->id;
        // dd($input['id']);
        Driver::where('id', Auth::user()->id)->update(['online_status' => $input['online_status']]);

        $vehicle = DriverVehicle::where('driver_id', Auth::user()->id)->first();
        // dd($vehicle);
        //$factory = (new Factory)->withServiceAccount(config_path().'/'.env('FIREBASE_FILE'));
        $factory = (new Factory())->withServiceAccount(config_path() . '/' . env('FIREBASE_FILE'))
            ->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();
        //$database = $firebase->getDatabase();
        $newPost = $database
            ->getReference('/vehicles/' . $vehicle->vehicle_type . '/' . $input['id'])
            ->update([
                'online_status' => (int) $input['online_status']
            ]);

        $newPost = $database
            ->getReference('/drivers/' . $input['id'])
            ->update([
                'online_status' => (int) $input['online_status']
            ]);

        return response()->json([
            "message" => 'Success',
            "status" => 1
        ]);
    }
    public function get_tutorials(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'country_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $data = DriverTutorial::where('country_id', $input['country_id'])->orderBy('id', 'DESC')->get();

        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function forget_password(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'phone_with_code' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $newPassword = rand(100000,999999);
        $options = [
            'cost' => 12,
        ];
        $cryptNewPassword = password_hash($newPassword, PASSWORD_DEFAULT, $options);

        if (Driver::where('phone_with_code', $input['phone_with_code'])->update(['password'=>$cryptNewPassword])) {
            $message ="Your new password is: " . $newPassword;
            $this->smsSe($input['phone_with_code'], $message);
            return response()->json([
                "message" => 'Success',
                "status" => 1,
                "newPassword" => $newPassword
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry something went wrong',
                "status" => 0
            ]);
        }
    }

    public function reset_password(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $options = [
            'cost' => 12,
        ];
        $password = password_hash($input["password"], PASSWORD_DEFAULT, $options);

        if (Driver::where('id', Auth::user()->id)->update(['password' => $password])) {
            return response()->json([
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry something went wrong',
                "status" => 0
            ]);
        }
    }

    public function get_vehicles(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'driver_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $data = DriverVehicle::where('driver_id', $input['driver_id'])->first();

        return response()->json([
            "result" => $data,
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function driver_dashboard(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $today_earnings = DriverEarning::where('driver_id', $input['id'])->whereDay('created_at', now()->day)->sum("amount");
        $result['today_earnings'] = number_format((float)$today_earnings, 2, '.', '');
        $result['today_bookings'] = Trip::where('driver_id', $input['id'])->where('status', '!=', '6')->where('status', '!=', '7')->whereDay('created_at', now()->day)->count();
        $result['today_completed_bookings'] = Trip::where('driver_id', $input['id'])->where('status', 5)->whereDay('updated_at', now()->day)->count();
        $result['online_status'] = Driver::where('id', $input['id'])->value('online_status');
        $result['vehicle_type'] = DriverVehicle::where('driver_id', $input['id'])->value('vehicle_type');

        if ($result) {
            return response()->json([
                "result" => $result,
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Something went wrong',
                "status" => 0
            ]);
        }
    }

    public function driver_ratings(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'trip_id' => 'required',
            'ratings' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $rating_update = Trip::where('id', $input['trip_id'])->update(['ratings' => $input['ratings']]);
        $trip = Trip::where('id', $input['trip_id'])->first();
        //print_r($trip);exit;
        if (is_object($trip)) {
            $this->driver_rating($trip->driver_id);
        } else {
            return response()->json([
                "message" => 'Something went wrong',
                "status" => 0
            ]);
        }
    }

    public function driver_rating($driver_id)
    {
        $ratings_data = Trip::where('driver_id', $driver_id)->where('ratings', '!=', '0')->get();
        //print_r($ratings_data);exit;
        $data_sum = Trip::where('driver_id', $driver_id)->get()->sum("ratings");
        $data = $data_sum / count($ratings_data);
        if ($data) {
            Driver::where('id', $driver_id)->update(['overall_ratings' => number_format((float)$data, 1, '.', ''), 'no_of_ratings' => count($ratings_data)]);
        }
        $ratings = Driver::where('id', $driver_id)->first();
        return response()->json([
            "result" => $ratings,
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function ride_completeion_mail(Request $request)
    {
        $input = $request->all();
        $ride_id = $id;
        $data = DB::table('trips')
            ->leftJoin('customers', 'customers.id', 'trips.customer_id')
            ->leftJoin('drivers', 'drivers.id', 'trips.driver_id')
            ->leftJoin('payment_methods', 'payment_methods.id', 'trips.payment_method')
            ->leftJoin('driver_vehicles', 'driver_vehicles.id', 'trips.vehicle_id')
            ->leftJoin('vehicle_categories', 'vehicle_categories.id', 'driver_vehicles.vehicle_type')
            ->leftJoin('booking_statuses', 'booking_statuses.id', 'trips.status')
            ->select('trips.*', 'customers.full_name as customer_name', 'customers.email as email', 'drivers.full_name as driver_name', 'drivers.profile_picture', 'payment_methods.payment as payment_method', 'driver_vehicles.brand as vehicle_brand', 'driver_vehicles.color', 'driver_vehicles.vehicle_name as vehicle_name', 'driver_vehicles.vehicle_number as vehicle_number', 'booking_statuses.status_name', 'vehicle_categories.vehicle_type')
            ->where('trips.id', $ride_id)
            ->first();
        $mail_header = array("data" => $data);
        $this->ride_completeion($mail_header, 'Ride Completed Successfully', $data->email);
    }
    public function vehicle_type_list(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'country_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $data = VehicleCategory::where('country_id', $input['country_id'])->get();

        return response()->json([
            "result" => $data,
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function vehicle_update(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'country_id' => 'required',
            'driver_id' => 'required',
            'vehicle_type' => 'required',
            'vehicle_image' => 'required',
            'brand' => 'required',
            'color' => 'required',
            'vehicle_name' => 'required',
            'vehicle_number' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $input['status'] = 1;
        $driver_vehicles = DriverVehicle::create($input);

        if (is_object($driver_vehicles)) {
            return response()->json([
                "result" => $driver_vehicles,
                "message" => 'Registered Successfully',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong !',
                "status" => 0
            ]);
        }
    }
    public function vehicle_image_upload(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/uploads/vehicle_images');
            $image->move($destinationPath, $name);
            return response()->json([
                "result" => 'vehicle_images/' . $name,
                "message" => 'Success',
                "status" => 1
            ]);
        }
    }

    public function register_query(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            // 'first_name' => 'required',
            // 'last_name' => 'required',
            'full_name' => 'required',
            'phone_number' => 'required|numeric|digits_between:9,20',
            'email' => 'required|email|regex:/^[a-zA-Z]{1}/',
            'description' => 'required'

        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $input['status'] = 1;
        $driver = DriverQuery::create($input);

        if ($driver) {
            return response()->json([
                "result" => $driver,
                "message" => 'Registered Successfully',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong !',
                "status" => 0
            ]);
        }
    }

    public function driver_trip()
    {

        $driver_id = Auth::user()->id;
        $trip = Trip::where('driver_id', $driver_id)->whereNotIn('status', [7, 8])->get()->last();
        if(is_null($trip)) {
            return response()->json([
                "message" => 'عذراً لا يوجد لديك رحلات حالياً',
                "status" => 0
            ]);
        } else {
            $customer = Customer::where('id', $trip->customer_id)->select('full_name', 'phone_with_code', 'profile_picture', 'rating')->get();
            $trip['price_wait'] = DailyFareManagement::where('id', 1)->value('price_wait');

            $inovice = array();
            $trip->start_time;
            $trip->end_time;
            $vehicle = DB::table('daily_fare_management')->where('id', 1)->first();
            $base_fare = number_format((float)$vehicle->base_fare, 2, '.', '');
            $distance = $this->get_distance($trip->trip_id);
            $price_per_km = number_format((float)$vehicle->price_per_km, 2, '.', '');
            $price_time = number_format((float)$vehicle->price_time, 2, '.', '');
            $interval = (strtotime($trip->end_time) - strtotime($trip->start_time)) / 60;
            $trip['arrive_driver'] = $trip['updated_at']->format('H:i');
            $time = strtotime($trip['updated_at']);
            // $trip['cancel_time'] = date("H:i",strtotime('+3 minutes',$time));
            $trip['cancel_time'] = 4;

            $inovice['sub_total'] = $price_per_km * $distance;
            $inovice['waiting_time'] = $price_time * $interval;
            $inovice['base_fare'] = $base_fare;
            $inovice['discount'] =  $trip->discount;
            $inovice['total'] =  $trip->total;


            if($trip->status < 6 ) {
                return response()->json([
                    "trip" => $trip,
                    "customer" => $customer,
                    "invoice" => $inovice,
                    "status" => 1
                ]);
            } else {
                return response()->json([
                    "message" => 'عذراً لا يوجد لديك رحلات حالياً',
                    "status" => 0
                ]);
            }
        }

    }

    public function driver_invite(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'referral_code' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $refferd = Driver::where('id', Auth::user()->id)->first();
        if ($refferd->referral_code == $input['referral_code']) {
            return response()->json([
                "message" => 'خطأ ، أنت تحاول ادخال الكود الخاص بك',
                "status" => 0
            ]);
        }
        if ($refferd->refered_by == null) {
            $driver = Driver::where('referral_code', $input['referral_code'])->first();
            if($driver) {
                $add_wallet = $driver->wallet + 1;
                Driver::where('referral_code', $input['referral_code'])->update(['wallet' => $add_wallet]);
                Driver::where('id', Auth::user()->id)->update(['refered_by'=>$input['referral_code']]);

                $invoice = new DriverWalletHistory();
                $invoice->driver_id = $driver->id;
                $invoice->type = 1;
                $invoice->amount = 1;
                $invoice->message = 'قمت بدعوة صديق و اضافة رصيد 1 دينار لمحفظتك';
                $invoice->save();

                $image = "image/tripaccept.png";
                // if ($driver->fcm_token) {
                //     $this->send_fcm('رصيد مكتسب', 'تمت إضافة رصيد 1 دينار لمحفظتك',$driver->fcm_token);
                //     $this->save_notifcation($driver->fcm_token,1,'رصيد مكتسب', 'تمت إضافة رصيد 1 دينار لمحفظتك',$image);
                // }
                return response()->json([
                    "message" => 'تم إضافة الدعوة بنجاح',
                    "status" => 1
                ]);
            } else {
                return response()->json([
                    "message" => 'يرجى التأكد من الكود المدخل',
                    "status" => 0
                ]);
            }
        } else {
            return response()->json([
                "message" => 'قمت بتفعيل كود الدعوة مسبقاً',
                "status" => 0
            ]);
        }
    }

    public function rate_customer(Request $request)
    {
        $input = $request->all();

        if(!isset($input['rate'])) {
            $input['rate']= 0;
        }
        $driver_id = Auth::user()->id;
        $trip = Trip::where('driver_id', $driver_id)->get()->last();
        // dd($customer_id);
        RateTrip::where('trip_id', $trip->id)->update(['driver_is_rate'=>1, 'customer_rate'=>$input['rate']]);
        $rates = RateTrip::where('customer_id', $trip->customer_id)->get();
        $i = 0;
        $total_rate = 0;

        foreach ($rates as $rate) {
            if($rate['customer_rate'] != 0) {
                $i++ ;
                $total_rate += $rate['customer_rate'];
            }
        }
        // dd($rate['driver_rate'] == 0);
        if($rates->count() == 1) {
            $rating = $total_rate;
        } else {
            if($i == 0) {
                $rating = $total_rate ;
            } else {
                $rating = $total_rate / $i ;
            }
        }
        Customer::where('id', $trip->customer_id)->update(['rating' => $rating]);
        return response()->json([
            "message" => "rate success",
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

    public function sendError($message)
    {
        $message = $message->all();
        $response['error'] = "validation_error";
        $response['message'] = implode('', $message);
        $response['status'] = "0";
        return response()->json($response, 200);
    }
}
