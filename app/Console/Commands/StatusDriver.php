<?php

namespace App\Console\Commands;

use App\Trip;
use Illuminate\Console\Command;
use Kreait\Firebase\Factory;

class StatusDriver extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:StatusDriver';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Status Driver';

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
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,"https://speedcar-jo.com/api/speedV1/driverStauts/change");
        curl_setopt($ch, CURLOPT_POST, 1);
        // curl_setopt($ch, CURLOPT_POSTFIELDS);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        curl_close ($ch);
        // $this->changeStatus();
    }


    public function changeStatus()
    {
        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();

        $drivers = $database->getReference('/drivers/')
        ->getSnapshot()->getValue();

        foreach ($drivers as $driver) {
            if($driver['booking_status']) {
                $excute = $this->checkDriverTrip($driver['driver_id']);
            }

        }
    }

    public function checkDriverTrip($driverId)
    {
        $driverTrip  = Trip::where('driver_id', $driverId)->get()->last();

        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();

        if (isset($driverTrip) && $driverTrip->status > 5) {
            $driver = $database
                ->getReference('/drivers/' . $driverId)
                ->update([
                    'booking_status' => 0
                ]);
        } else {
            if (!isset($driverTrip)) {
                $driver = $database
                    ->getReference('/drivers/' . $driverId)
                    ->update([
                        'booking_status' => 0
                    ]);
            }
        }
    }
}
