<?php

namespace App\Http\Controllers;



use App\Country;
use App\Currency;
use App\Driver;
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
        $input['phone_with_code'] = $input['country_code'].$request->phone_number;
        $input['currency'] = 'JOD';
        $input['status'] = 0;
        $input['daily'] = 1;
        $input['rental'] = 0;
        $input['outstation'] = 0;

        if ($request->hasFile('profile_picture')){
            $image = $request->file('profile_picture');
            $imageName = $image->getClientOriginalName();
            $request->profile_picture->move(public_path('/uploads/drivers'), $imageName);
            $input['profile_picture'] = 'drivers/'.$imageName;
        }

        if ($request->hasFile('id_proof')){
            $image = $request->file('id_proof');
            $proofImage = $image->getClientOriginalName();
            $request->id_proof->move(public_path('/uploads/image'), $proofImage);
            $input['id_proof'] =$proofImage;
        }

        if ($request->hasFile('vehicle_image')){
            $image = $request->file('vehicle_image');
            $vehicle_image = $image->getClientOriginalName();
            $request->vehicle_image->move(public_path('/uploads/captain_vehicle_image'), $vehicle_image);
            $input['vehicle_image'] =$vehicle_image;
        }
        Driver::create($input);
        Driver::latest()->first()->update([ 'profile_picture' => 'drivers/'.$imageName ]);
        Driver::latest()->first()->update([ 'id_proof' => 'image/'.$proofImage ]);
        Driver::latest()->first()->update([ 'vehicle_image' => 'captain_vehicle_image/'.$vehicle_image ]);



        //$factory = (new Factory)->withServiceAccount(config_path().'/'.env('FIREBASE_FILE'));
        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();
        //$database = $firebase->getDatabase();

        return redirect()->back()->with('captain_registered','تم إرسال البيانات بنجاح');


    }

}
