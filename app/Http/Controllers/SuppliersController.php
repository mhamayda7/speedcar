<?php

namespace App\Http\Controllers;

use App\Driver;
use App\DriverWalletHistory;
use App\Models\Supplier;
use App\Models\Suppliers_history;
use App\NotificationMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class SuppliersController extends Controller
{
    public function index()
    {

    }

    public function showLogin()
    {
        return view('supplier.supplier_login');
    }



    public function customLogin(Request $request)
    {
        $inputVal = $request->all();

        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if(Auth::guard('supplier')->attempt(array('email' => $inputVal['email'], 'password' => $inputVal['password']))){

            return redirect()->route('dashboard');

        }else{
            return back()->withInput($request->only('email'));
        }
    }


    public function dashboard()
    {
        $supplier = Auth::guard('supplier')->user();
        return view('supplier.index',compact('supplier'));
    }

    public function add_fund_page()
    {
        return view('supplier.search_drivers');
    }

    public function get_driver(Request $request)
    {
        $request->validate([
            'phone'=>'required|numeric|digits_between:9,20'
        ]);
        $phone_number = $request->phone;
        $driver = Driver::where('phone_number',$phone_number)->first();
        if ($driver == null){
            return redirect()->back()->with('not_found','الرقم المدخل غير مرتبط بأي سائق');
        }else
        return view('supplier.add_fund',compact('driver'));
    }

    public function add_fund(Request $request)
    {

        $supplier_name = auth()->guard('supplier')->user()->name;
        $supplier = auth()->guard('supplier')->user();

        $driver = Driver::findOrFail($request->id);
        $amount = $request->amount;

        if ($supplier->wallet < $amount){
            $empty= Session::flash('empty','لا يوجد رصيد كافي في محفظتك ! ');
            return view('supplier.add_fund',compact('driver'))->with('x',$empty);
        }else
        $driver_id = $driver->id;
        $driver_name = $driver->full_name;

        $driver->wallet += $amount;
        $supplier->wallet -= $amount;
        $supplier->save();
        $driver->save();
        if ($driver->fcm_token) {
            $title ="شحن رصيد";
            $description ="تم شحن رصيد محفظتك بقيمة " . $amount . " دينار";
            $image = "";
            $this->send_fcm($title,$description,$driver->fcm_token);
            $this->save_notifcation($driver_id,2,$title,$description,$image);
        }
        $history = new DriverWalletHistory();
        $history->driver_id = $driver_id;
        $history->type = 0;
        $history->transaction_type = 2;
        $history->message = "شحن رصيد محفظة";
        $history->amount = $amount;
        $history->save();
        $supplier_history = new Suppliers_history();
        $supplier_history->supplier_name = $supplier_name;
        $supplier_history->driver_name = $driver_name;
        $supplier_history->amount = $amount;
        $supplier_history->save();
        $x= Session::flash('funded','تم شحن المبلغ بنجاح ');
        return view('supplier.add_fund',compact('driver'))->with('x',$x);

    }


    public function billing()
    {
        $transactions = Suppliers_history::latest()->paginate(15);
        return view('supplier.billing',compact('transactions'));
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
}
