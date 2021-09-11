<?php

namespace App\Http\Controllers;



use App\Country;
use App\Currency;
use App\Driver;
use Illuminate\Http\Request;
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
        $input['country_code'] = 970;
        $input['phone_with_code'] = $request->phone_number + 970;
        $input['currency'] = 'JOD';
        $input['daily'] = 1;
        $input['rental'] = 0;
        $input['outstation'] = 0;

        if ($request->hasFile('profile_picture')){
            $image = $request->file('profile_picture');
            $imageName = $image->getClientOriginalName();
            $request->profile_picture->move(public_path('/uploads/drivers'), $imageName);
            $input['profile_picture'] =($imageName);
        }

//        if ($request->hasFile('profile_picture')) {
//            $image = $request->file('profile_picture');
//            $name = time().'.'.$image->getClientOriginalExtension();
//            $destinationPath = public_path('/uploads/drivers');
//            $image->move($destinationPath, $name);
//            $input['profile_picture']='drivers/'.$name;
//
//        }


        if ($request->hasFile('id_proof')){
            $image = $request->file('id_proof');
            $imageName = $image->getClientOriginalName();
            $request->id_proof->move(public_path('/uploads/image'), $imageName);
            $input['id_proof'] =($imageName);
        }

         Driver::create($input);
        dd($input);


        //$factory = (new Factory)->withServiceAccount(config_path().'/'.env('FIREBASE_FILE'));
        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();
        //$database = $firebase->getDatabase();

        return redirect()->back()->with('captain_registered','تم إرسال البيانات بنجاح');


    }

}
