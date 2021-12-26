<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
     protected $fillable = [
        'id','group_customer','promo_code','promo_type','description','discount','count_used','status','valid_to','created_at','updated_at'
    ];
}
