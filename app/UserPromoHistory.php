<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserPromoHistory extends Model
{
     protected $fillable = [
        'id','customer_id','promo_id','trip_id','created_at','updated-at',
    ];
}
