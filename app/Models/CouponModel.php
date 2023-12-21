<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponModel extends Model
{
    use HasFactory;

    protected $table    = 'db_coupons';
    protected $fillable = ['id', 'store_id', 'code', 'name', 'description', 'value', 'type', 'expire_date', 'status', 'created_by',
                            'created_date', 'created_time', 'system_name', 'system_ip'];
    public $timestamps  = false;
}
