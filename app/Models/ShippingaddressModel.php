<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingaddressModel extends Model
{
    use HasFactory;

    protected $table        = 'db_shippingaddress';
    protected $fillable     = ['id', 'store_id', 'country_id', 'state_id', 'city', 'postcode', 'address', 'status', 'customer_id', 'location_link'];
    public $timestamps      = false;

    public function customers(){
        return $this->belongsToMany(CustomerModel::class, 'customers', 'id','shippingaddress_id');
    }
}
