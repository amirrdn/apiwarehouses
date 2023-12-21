<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerModel extends Model
{
    use HasFactory;

    protected $table        = 'db_customers';
    protected $fillable     = ['id', 'store_id', 'count_id', 'customer_code', 'customer_name', 'mobile', 'phone',
                                'email', 'gstin', 'tax_number', 'vatin', 'opening_balance', 'sales_due',
                                'sales_return_due', 'country_id', 'state_id', 'city', 'postcode', 'address',
                                'ship_country_id', 'ship_state_id', 'ship_city', 'ship_postcode', 'ship_address',
                                'system_ip', 'system_name', 'created_date', 'created_time', 'created_by', 'company_id',
                                'status', 'location_link', 'attachment_1', 'price_level_type', 'price_level', 'delete_bit',
                                'tot_advance', 'credit_limit', 'shippingaddress_id'];
    public $timestamps      = false;

    public function shippingaddress()
    {
        return $this->belongsToMany(ShippingaddressModel::class, 'db_customers', 'id', 'shippingaddress_id');
    }
    public function countryship()
    {
        return $this->belongsToMany(CountryModel::class, 'db_shippingaddress', 'id', 'country_id');
    }
    public function customer_store()
    {
        return $this->belongsToMany(StoreModel::class, 'db_customers', 'id', 'store_id');
    }
}
