<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Model
{
    use HasFactory, HasApiTokens;
    protected $fillable = [
        'id', 'country_id', 'full_name', 'phone_number','email','password','profile_picture','status', 'country_code','currency_short_code', 'phone_with_code', 'fcm_token', 'currency', 'wallet','gender', 'otp'
    ];
}
