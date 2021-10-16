<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;
use App\Customer;
use App\CustomerSosContact;
use App\Currency;
use App\Country;
use App\Models\Package;
use App\PaymentMethod;
use App\VehicleCategory;
use App\Models\CustomerOffer;
use App\CustomerWalletHistory;
use App\InstantOffer;
use App\PromoCode;
use App\AppSetting;
use App\Models\Point;
use App\Models\User;
use App\NotificationMessage;
use App\Trip;
use Cartalyst\Stripe\Stripe;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
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
        $customer = Customer::where('phone_with_code', $input['phone_with_code'])->first();

        if (is_object($customer)) {
            $data['is_available'] = 1;
            $data['otp'] = "";
            return response()->json([
                "result" => $data,
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            $data['is_available'] = 0;
            $data['otp'] = rand(1000, 9999);
            $message = "Hi" . env('APP_NAME') . "  , Your OTP code is:" . $data['otp'];
            //$message = "Hi Esycab"." , Your OTP code is:".$data['otp'];
            $this->sendSms($input['phone_with_code'], $message);
            return response()->json([
                "result" => $data,
                "message" => 'Success',
                "status" => 1
            ]);
        }
    }

    public function forgot(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'phone_with_code' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $customer = Customer::where('phone_with_code', $input['phone_with_code'])->first();

        if (is_object($customer)) {
            $otp = rand(1000, 9999);
            $message = "Hi" . env('APP_NAME') . " , Your OTP code is:" . $otp;
            $this->sendSms($input['phone_with_code'], $message);
            return response()->json([
                "result" => $otp,
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Please enter valid phone number',
                "status" => 0
            ]);
        }
    }

    public function reset_password(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'phone_with_code' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $options = [
            'cost' => 12,
        ];
        $input['password'] = password_hash($input["password"], PASSWORD_DEFAULT, $options);

        if (Customer::where('phone_with_code', $input['phone_with_code'])->update($input)) {
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

        if (Customer::where('phone_with_code', $input['phone_with_code'])->update(['password'=>$cryptNewPassword])) {
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

    public function register(Request $request)
    {
        $input = $request->all();
        $refered_by = $input['referral_code'];

        $validator = Validator::make($input, [
            // 'first_name' => 'required',
            // 'last_name' => 'required',
            'full_name' => 'required',
            'country_code' => 'required',
            // 'phone_with_code' => 'required',
            'phone_number' => 'required|numeric|digits_between:9,20|unique:customers,phone_number',
            'email' => 'required|email|regex:/^[a-zA-Z]{1}/|unique:customers,email',
            'password' => 'required',
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
        $input['referral_code'] = '';
        $referrel_id = Customer::where('referral_code', $refered_by)->value('id');
        if ($referrel_id) {
            $input['refered_by'] = $refered_by;
        } else {
            $input['refered_by'] = '';
        }
        $data = Country::where('phone_code', $input['country_code'])->value('id');
        $input['country_id'] = $data;
        $input['currency'] = Currency::where('country_id', $input['country_id'])->value('currency');
        $input['currency_short_code'] = Currency::where('country_id', $input['country_id'])->value('currency_short_code');
        $input['profile_picture'] = "customers/avatar.jpg";
        $input['phone_with_code'] = $input['country_code'].$input['phone_number'];
        $otp = rand(1000, 9999);
        $input['otp']=$otp;
        $customer = Customer::create($input);

        $phone = '+'.$input['phone_with_code'];

        $message = "Hi " . env('APP_NAME') . "  , Your OTP code is:" . $otp;

        // $this->sendSms($phone, $message);
        // $this->sendSms($input['phone_with_code'], $message);
        // $this->smsSe($phone, $message);

        //$database = $firebase->getDatabase();


        if (is_object($customer)) {

            if ($refered_by != '' && $referrel_id) {

                $referral_amount = AppSetting::where('id', 1)->value('referral_amount');
                //print_r($referral_amount);exit;
                $existing_wallet_amount = Customer::where('referral_code', $refered_by)->value('wallet');
                $wallet = $existing_wallet_amount + $referral_amount;
                Customer::where('referral_code', $refered_by)->update(['wallet' => $wallet]);
                Customer::where('id', $customer->id)->update(['refered_by' => $referrel_id]);
                $content = "Referral Bonus";
                $refered_country_id = Customer::where('id', $referrel_id)->value('country_id');
                CustomerWalletHistory::create(['country_id' => $refered_country_id, 'customer_id' => $referrel_id, 'type' => 3, 'message' => $content, 'amount' => $referral_amount, 'transaction_type' => 1]);
            }

            // $customer->referral_code = 'CAB'.str_pad($customer->id,5,"0",STR_PAD_LEFT);


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
                $count = DB::table('customers')->where('referral_code', '=', $random)->count();
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

            $customer->referral_code = $random;
            Customer::where('id', $customer->id)->update(['referral_code' => $customer->referral_code]);
            $token = $customer->createToken('name')->plainTextToken;

            $factory = (new Factory())->withServiceAccount(config_path().'/'.env('FIREBASE_FILE'))
                                ->withDatabaseUri(env('FIREBASE_DB'));
            $database = $factory->createDatabase();
            $newPost = $database
                ->getReference('/customers/' . $customer->id)
                ->update([
                    'booking_id' => 0,
                    'booking_status' => 0,
                    'customer_name' => $customer->full_name
                    // 'customer_name' => $customer->first_name
                ]);

            return response()->json([
                "result" => $customer,
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

    function unique_random($chars = 5)
    {
    }

    public function referral(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
            'referral_code' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        Customer::where('referral_code', $input['referral_code'])->update;
        Customer::where('id', $input['customer_id'])->update(['refered_by'=>$input['referral_code']]);

    }
    /*public function register(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'first_name' => 'required',
            'last_name' => 'required',
            'country_code' => 'required',
            'phone_with_code' => 'required',
            'phone_number' => 'required|numeric|digits_between:9,20|unique:customers,phone_number',
            'email' => 'required|email|regex:/^[a-zA-Z]{1}/|unique:customers,email',
            'password' => 'required',
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
        $data = Country::where('phone_code',$input['country_code'])->value('id');
        $input['country_id'] = $data;
        $input['currency'] = Currency::where('country_id',$input['country_id'])->value('currency');
        $input['currency_short_code'] = Currency::where('country_id',$input['country_id'])->value('currency_short_code');
        $customer = Customer::create($input);

        //$factory = (new Factory)->withServiceAccount(config_path().'/'.env('FIREBASE_FILE'));
        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();
        //$database = $firebase->getDatabase();


        if (is_object($customer)) {

            $newPost = $database
            ->getReference('customers/'.$customer->id)
            ->update([
                'booking_id' => 0,
                'booking_status' => 0,
                 'customer_name' => $customer->first_name
            ]);

            return response()->json([
                "result" => $customer,
                "message" => 'Registered Successfully',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong !',
                "status" => 0
            ]);
        }

    }*/

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
        $customer = Customer::where('phone_with_code', $credentials['phone_with_code'])->first();

        if (!($customer)) {
            return response()->json([
                "message" => 'Invalid phone number or password',
                "status" => 0
            ]);
        }

        if (Hash::check($credentials['password'], $customer->password)) {
            if ($customer->status == 1) {
                Customer::where('id', $customer->id)->update(['fcm_token' => $input['fcm_token']]);
                $token = $customer->createToken('name')->plainTextToken;
                return response()->json([
                    "result" => $customer,
                    "token" => $token,
                    "message" => 'Success',
                    "status" => 1
                ]);
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
        Customer::where('id', Auth::user()->id)->update(['fcm_token'=> null]);
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            "message" => 'Success Sign Out',
            "status" => 1
        ]);
    }

    public function profile_picture(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif,svg'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        if ($request->hasFile('profile_picture')) {
            $image = $request->file('profile_picture');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/uploads/images');
            $image->move($destinationPath, $name);
            if (Customer::where('id', $input['customer_id'])->update(['profile_picture' => 'images/' . $name])) {
                return response()->json([
                    // "result" => Customer::select('id', 'first_name', 'last_name', 'phone_with_code', 'email', 'profile_picture', 'password', 'status')->where('id', $input['customer_id'])->first(),
                    "result" => Customer::select('id', 'full_name', 'phone_with_code', 'email', 'profile_picture', 'password', 'status')->where('id', $input['customer_id'])->first(),
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

    public function profile(Request $request)
    {
        // $input = $request->all();
        // $validator = Validator::make($input, [
        //     'customer_id' => 'required'
        // ]);

        // if ($validator->fails()) {
        //     return $this->sendError($validator->errors());
        // }

        // $result = Customer::select('id', 'first_name', 'last_name', 'phone_with_code', 'gender', 'email', 'status')->where('id', $input['customer_id'])->first();
        $result = Customer::select('id', 'full_name', 'phone_with_code', 'gender', 'email', 'points', 'wallet' ,'status', 'referral_code')->where('id', Auth::user()->id)->first();

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
        // $input = $request->all();
        // $validator = Validator::make($input, [
        //     'id' => 'required'
        // ]);

        // if ($validator->fails()) {
        //     return $this->sendError($validator->errors());
        // }
        $input['id']= Auth::user()->id;

        if ($request->password) {
            $options = [
                'cost' => 12,
            ];
            $input['password'] = password_hash($input["password"], PASSWORD_DEFAULT, $options);
            $input['status'] = 1;
        } else {
            unset($input['password']);
        }

        if (Customer::where('id', $input['id'])->update($input)) {
            return response()->json([
                // "result" => Customer::select('id', 'first_name', 'last_name', 'email', 'gender', 'phone_with_code')->where('id', $input['id'])->first(),
                "result" => Customer::select('id', 'full_name', 'email', 'gender', 'phone_with_code')->where('id', $input['id'])->first(),
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

    public function get_wallet(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $data['wallet'] = Customer::where('id', $input['id'])->value('wallet');

        $data['wallet_histories'] = CustomerWalletHistory::where('customer_id', $input['id'])->orderBy('id', 'desc')->get();

        if ($data) {
            return response()->json([
                "result" => $data,
                "count" => count($data['wallet_histories']),
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

    public function add_wallet(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required',
            'country_id' => 'required',
            'amount' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $data['customer_id'] = $input['id'];
        $data['country_id'] = $input['country_id'];
        $data['type'] = 1;
        $data['message'] = "Added to wallet";
        $data['amount'] = $input['amount'];
        $data['transaction_type'] = 1;
        CustomerWalletHistory::create($data);

        $old_wallet = Customer::where('id', $input['id'])->value('wallet');
        $new_wallet = $old_wallet + $input['amount'];
        Customer::where('id', $input['id'])->update(['wallet' => $new_wallet]);
        return response()->json([
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function get_vehicle_categories(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'country_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $data = VehicleCategory::where('country_id', $input['country_id'])->get();

        //$data = VehicleCategory::where('country_id',$country)->get();
        //print_r($country_id); exit;

        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function payment_method(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'country_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        // $data = PaymentMethod::where('status',1)->where('payment_type',1)->where('country_id',$input['country_id'])->get();
        $data = PaymentMethod::where('status', 1)->where('country_id', $input['country_id'])->get();

        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function wallet_payment_methods(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'country_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $data = PaymentMethod::where('status', 1)->whereNotIn('payment_type', [1, 2, 3])->where('country_id', $input['country_id'])->get();

        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function customer_offers(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $data = CustomerOffer::where('customer_id', $input['customer_id'])->where('status', 1)->orderBy('view_status', 'DESC')->get();

        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function customer_invite(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'referral_code' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $refferd = Customer::where('id', Auth::user()->id)->value('refered_by');
        if ($refferd == null) {
            $customer_point = Customer::where('referral_code', $input['referral_code'])->value('points');
            $customer_id = Customer::where('referral_code', $input['referral_code'])->value('id');
            $referral_bonus = DB::table('referral_settings')->where('id',1)->value('referral_bonus');
            // dd($referral_settings);
            $points_add = $referral_bonus + $customer_point;
            Customer::where('referral_code', $input['referral_code'])->update(['points' => $points_add ]);
            Customer::where('id', Auth::user()->id)->update(['refered_by' => $input['referral_code'] ]);


            $point = new Point;
            $point->customer_id = $customer_id;
            $point->trip_id = 0;
            $point->type = 1;
            $point->point = intval($points_add);
            $point->details = "دعوة صديق";
            $point->icon = "rewards/taxi.png";
            $point->save();

            $customer_fcm = Customer::where('referral_code', $input['referral_code'])->value('fcm_token');
            $image = "image/tripaccept.png";

            if ($customer_fcm) {
                $this->send_fcm('نقاط مكتسبة', 'قمت بدعوة صديق و اضافة'. $points_add . ' نقاط', $customer_fcm);
                $this->save_notifcation($customer_id,1,'نقاط مكتسبة', 'قمت بدعوة صديق و اضافة',$image);
            }
            return response()->json([
                "message" => 'Success',
                "status" => 1
            ]);
        } else {

            return response()->json([
                "message" => 'قمت باضافة كود الدعوة من قبل',
                "status" => 1
            ]);
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

    public function update_view_status(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
            'offer_id' => 'required',
            'status' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        CustomerOffer::where('customer_id', $input['customer_id'])->where('id', $input['offer_id'])->update(['view_status' => $input['status']]);
        $offer = CustomerOffer::where('id', $input['offer_id'])->first();
        if (is_object($offer) && $offer->type == 1) {
            $instant = InstantOffer::where('id', $offer->ref_id)->first();
            $data['country_id'] = Customer::where('id', $input['customer_id'])->value('country_id');
            $data['customer_id'] = $input['customer_id'];
            $data['promo_name'] = $instant->offer_name;
            $data['promo_code'] = $this->getToken(8);
            $data['description'] = $instant->offer_description;
            $data['promo_type'] = $instant->discount_type;
            $data['discount'] = $instant->discount;
            $data['redemptions'] = 1;
            $data['status'] = 1;
            PromoCode::create($data);
        }
        return response()->json([
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function crypto_rand_secure($min, $max)
    {
        $range = $max - $min;
        if ($range < 1) return $min; // not so random...
        $log = ceil(log($range, 2));
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd > $range);
        return $min + $rnd;
    }

    public function getToken($length)
    {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet .= "0123456789";
        $max = strlen($codeAlphabet); // edited

        for ($i = 0; $i < $length; $i++) {
            $token .= $codeAlphabet[$this->crypto_rand_secure(0, $max - 1)];
        }

        return $token;
    }

    public function stripe_payment(Request $request)
    {
        $input = $request->all();
        $stripe = new Stripe();
        $currency_code = AppSetting::value('currency_short_code');

        try {
            $charge = $stripe->charges()->create([
                'source' => $input['token'],
                'currency' => $currency_code,
                'amount'   => $input['amount'],
                'description' => 'For booking'
            ]);

            $data['order_id'] = 0;
            $data['customer_id'] = $input['customer_id'];
            $data['payment_mode'] = 2;
            $data['payment_response'] = $charge['id'];

            return response()->json([
                "result" => $charge['id'],
                "message" => 'Success',
                "status" => 1
            ]);
        } catch (customException $e) {
            return response()->json([
                "message" => 'Sorry something went wrong',
                "status" => 0
            ]);
        }
    }

    public function add_sos_contact(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
            'name' => 'required',
            'phone_number' => 'required|numeric|digits_between:9,20'

        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $input['status'] = 1;
        $contact = CustomerSosContact::create($input);

        if ($contact) {
            return response()->json([
                "result" => $contact,
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
    public function delete_sos_contact(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
            'contact_id' => 'required'


        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        CustomerSosContact::where('customer_id', $input['customer_id'])->where('id', $input['contact_id'])->delete();
        return response()->json([
            "message" => 'Registered Successfully',
            "status" => 1
        ]);
    }
    public function sos_contact_list(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required'

        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $contact = CustomerSosContact::where('customer_id', $input['customer_id'])->get();

        if ($contact) {
            return response()->json([
                "result" => $contact,
                "message" => 'success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong !',
                "status" => 0
            ]);
        }
    }

    public function sos_sms(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
            'booking_id' => 'required',
            'latitude' => 'required',
            'longitude' => 'required'

        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $customer = Customer::where('id', $input['customer_id'])->first();
        $contacts = CustomerSosContact::where('customer_id', $input['customer_id'])->get();
        $trip = Trip::where('id', $input['booking_id'])->first();
        $location = "https://maps.google.com/?ll=" . $input['latitude'] . "," . $input['longitude'];
        // $message = "Hi, this is  " . $customer->first_name . "  i believe i am in danger near " . $location . " . Please help me by contacting the authorities.";
        $message = "Hi, this is  " . $customer->full_name . "  i believe i am in danger near " . $location . " . Please help me by contacting the authorities.";

        $country_code = $customer->country_code;
        if (count($contacts) > 0) {
            foreach ($contacts as $key => $value) {
                $this->sendSms($country_code . $value->phone_number, $message);
            }
            return response()->json([
                "message" => 'SOS activated',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Please add emergency numbers in sos settings page',
                "status" => 0
            ]);
        }
    }

    public function get_gender(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $gender = Customer::where('id', $input['customer_id'])->value('gender');

        return response()->json([
            "result" => $gender,
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function get_package()
    {
        $data = Package::all();
        return response()->json([
            "result" => $data,
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function sendError($message)
    {
        $message = $message->all();
        $response['error'] = "validation_error";
        $response['message'] = implode('', $message);
        $response['status'] = "0";
        return response()->json($response, 200);
    }

    public function sendFcm(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $customer_fcm = Customer::where('id', $input['customer_id'])->value('fcm_token');
        if ($customer_fcm) {
            $this->send_fcm('نقاط مكتسبة', 'قمت بدعوة صديق و اضافة' . ' نقاط', $customer_fcm);
        }
        return response()->json([
            "message" => 'Success',
            "status" => 1
        ]);
    }
}
