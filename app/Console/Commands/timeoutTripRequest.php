<?php

namespace App\Console\Commands;

use App\TripRequest;
use Illuminate\Console\Command;
use Kreait\Firebase\Factory;

class timeoutTripRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'TripRequest:timeout';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'timeoutTripRequest';

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
        $timeNow = time();
        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();
        $triprequests = TripRequest::where('status', 2)->get();
        foreach ($triprequests as $triprequest) {
            $timeInterval = ($timeNow - strtotime($triprequest->created_at))/60;
            if ($timeInterval > 2) {
                $triprequest->update(['status' => 4]);
                $newPost = $database
                ->getReference('/triprequest/' . $triprequest->id)
                ->remove();
            }
        }
    }
}
