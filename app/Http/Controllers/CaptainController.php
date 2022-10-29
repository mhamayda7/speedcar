<?php

namespace App\Http\Controllers;



use App\Country;
use App\Currency;
use App\Driver;
use App\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Factory;

class CaptainController extends Controller
{
    public function view_register()
    {
        return view('register_captain');
    }

    public function register(Request $request)
    {

        $input = $request->all();
        $request->validate([
            'full_name' => 'required',
            'phone_number' => 'required|numeric|digits_between:9,20|unique:drivers,phone_number',
            'email' => 'required|email|regex:/^[a-zA-Z]{1}/|unique:drivers,email',
            'password' => 'required',
            'gender' => 'required',
            'date_of_birth' => 'required',
            'licence_number' => 'required',
        ]);
        //            ::make($input, [

        //            'fcm_token' => 'required'
        //        ]);


        $options = [
            'cost' => 12,
        ];

        $input['password'] = password_hash($input["password"], PASSWORD_DEFAULT, $options);
        $input['status'] = 1;
        $input['fcm_token'] = 0000;
        $input['country_id'] = 1;
        $input['country_code'] = 962;

        $phone_array = str_split($request->phone_number);

        if ($phone_array[0] == 0) {
            $phone_without = substr($request->phone_number, 1);
        } else {
            $phone_without = $request->phone_number;
        }

        $input['phone_with_code'] = $input['country_code'] . $phone_without;
        $input['currency'] = 'JOD';
        $input['status'] = 0;
        $input['daily'] = 1;
        $input['rental'] = 0;
        $input['outstation'] = 0;

        if ($request->hasFile('profile_picture')) {
            $image = $request->file('profile_picture');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $request->profile_picture->move(public_path('/uploads/drivers'), $imageName);
            $input['profile_picture'] = 'drivers/' . $imageName;
        }

        if ($request->hasFile('id_proof')) {
            $image = $request->file('id_proof');
            $proofImage = time() . '_' . $image->getClientOriginalName();
            $request->id_proof->move(public_path('/uploads/image'), $proofImage);
            $input['id_proof'] = $proofImage;
        }

        if ($request->hasFile('vehicle_image')) {
            $image = $request->file('vehicle_image');
            $vehicle_image = time() . '_' . $image->getClientOriginalName();
            $request->vehicle_image->move(public_path('/uploads/captain_vehicle_image'), $vehicle_image);
            $input['vehicle_image'] = $vehicle_image;
        }

        if ($request->hasFile('vehicle_licence')) {
            $image = $request->file('vehicle_licence');
            $vehicle_licence = time() . '_' . $image->getClientOriginalName();
            $request->vehicle_licence->move(public_path('/uploads/captain_vehicle_licence'), $vehicle_licence);
            $input['vehicle_licence'] = $vehicle_licence;
        }
        $driver = Driver::create($input);
        Driver::latest()->first()->update(['profile_picture' => 'drivers/' . $imageName]);
        Driver::latest()->first()->update(['id_proof' => 'image/' . $proofImage]);
        Driver::latest()->first()->update(['vehicle_image' => 'captain_vehicle_image/' . $vehicle_image]);
        Driver::latest()->first()->update(['vehicle_licence' => 'captain_vehicle_licence/' . $vehicle_licence]);



        //$factory = (new Factory)->withServiceAccount(config_path().'/'.env('FIREBASE_FILE'));
        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();
        if (is_object($driver)) {
            $newPost = $database
                ->getReference('drivers/' . $driver->id)
                ->update([
                    'driver_name' => $input['full_name'],
                    'status' => $input['status'],
                    'lat' => 0,
                    'lng' => 0,
                    'online_status' => 0,
                    'booking_status' => 0,
                    'accuracy' => 0,
                    'heading' => 0,
                    "distance" => 0,
                    "driver_id" => $driver->id,
                    "startlat" => 31.4207007,
                    "startlng" => 0,
                ]);
        }
        //$database = $firebase->getDatabase();

        return view('thankyou')->with('captain_registered', 'تم إرسال البيانات بنجاح');
    }

}
