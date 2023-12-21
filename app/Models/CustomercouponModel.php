<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomercouponModel extends Model
{
    use HasFactory;

    protected $table        = 'db_customer_coupons';
    protected $fillabel     = ['id', 'store_id', 'code', 'name', 'description', 'value', 'type', 'expire_date', 'status',
                                'created_by', 'created_date', 'created_time', 'system_name', 'system_ip', 'customer_id',
                                'coupon_id'];
    public $timestamps      = false;

    public function couponstore()
    {
        return $this->belongsToMany(StoreModel::class, 'db_customer_coupons', 'id', 'store_id');
    }
    public function customercoupon()
    {
        return $this->belongsToMany(CustomerModel::class, 'db_customer_coupons', 'id', 'customer_id');
    }
    public function coupon()
    {
        return $this->belongsToMany(CouponModel::class, 'db_customer_coupons', 'id', 'coupon_id');
    }
}
