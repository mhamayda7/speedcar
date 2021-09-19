<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = [
        'id', 'full_name','phone_number','country_id','phone_with_code','email','gender','password',
        'date_of_birth','licence_number','fcm_token','currency','overall_ratings', 'no_of_ratings',
        'daily', 'rental', 'outstation','status','profile_picture','id_proof','vehicle_image','vehicle_type','vehicle_model'
    ];
}
