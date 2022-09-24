<?php

namespace App\Console\Commands;

use App\Customer;
use App\Driver;
use App\Models\DriverTripRequest;
use App\Models\TripRequest;
use Exception;
use Illuminate\Console\Command;
use Kreait\Firebase\Factory;

class test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command test';

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
        $this->runChangeDrive();
    }


    public function runChangeDrive() {
        // sleep(time() % 30);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://speedcar-jo.com/api/speedV1/changeDrive",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            // CURLOPT_POSTFIELDS => "sender=Speed Car&mobile=" . $phone . "&content=".$message,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer qrVfXQ54M5MBDPPuVtUzUoxM0Fnh7hq9ULkSj8r9"
            ),
        ));
    }
}
