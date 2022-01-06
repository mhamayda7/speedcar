<?php

namespace App\Console\Commands;

use App\TripRequest;
use Illuminate\Console\Command;
use Kreait\Firebase\Factory;
use App\Models\DriverTripRequest;



class ChangeDriver extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ChangeDriver:timeout';

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
        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();
        $value = $database->getReference('/triprequest/')
                            ->getValue();

        // $this->change_driver();
        // sleep(19);
        // $this->change_driver();
        // sleep(19);
        // $this->change_driver();
    }

    public function change_driver($trip_id, $driver_id) {

        $input['driver_id'] = $driver_id;

        $trip = TripRequest::where('id', $input['trip_id'])->first();

        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();
        $newPost = $database
            ->getReference('/drivers/' . $input['driver_id'])
            ->update([
                'booking_status' => 0
            ]);

        $newPost = $database
            ->getReference('/vehicles/' . $trip->vehicle_type . '/' . $input['driver_id'])
            ->update([
                'booking_id' => 0,
                'booking_status' => 0,
                'pickup_address' => "",
                'drop_address' => "",
                'total' => 0,
                'customer_name' => "",
                "static_map" => "",
                'trip_type' => ""
            ]);

        if ($trip->booking_type == 1) {
            TripRequest::where('id', $input['trip_id'])->update(['status' => 4]);
        }

        $data['driver_id'] = $input['driver_id'];
        $data['trip_request_id'] = $input['trip_id'];
        $data['status'] = 4;

        DriverTripRequest::create($data);
        $this->find_driver($input['trip_id']);
        return response()->json([
            "message" => 'Success',
            "status" => 1
        ]);
    }
}
