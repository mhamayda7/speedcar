<?php

namespace App\Console\Commands;

use App\Driver;
use App\DriverVehicle;
use Illuminate\Console\Command;
use Kreait\Firebase\Factory;

class changeStatusDriver extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'changeStatusDriver';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'changeStatusDriver';

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
                    Driver::where('id', $driver['driver_id'])->update([
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
}
