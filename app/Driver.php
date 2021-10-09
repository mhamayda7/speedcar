<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class Driver extends Model
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'id', 'full_name','phone_number','country_id','phone_with_code','email','gender','password',
        'date_of_birth','licence_number','fcm_token','currency','overall_ratings', 'no_of_ratings',
        'daily', 'rental', 'outstation','status','profile_picture','id_proof','vehicle_image','vehicle_type','vehicle_model','vehicle_licence','note'
    ];
}
