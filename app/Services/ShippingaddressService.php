<?php

namespace App\Services;
use App\Models\ShippingaddressModel;
/**
 * Class ShippingaddressService.
 */
class ShippingaddressService
{
    public function ShippRelations()
    {
        return ShippingaddressModel::from('db_shippingaddress as shp')
        ->leftjoin('db_country as cn', 'shp.country_id', 'cn.id')
        ->leftjoin('db_states as sts', 'shp.state_id', 'sts.id');
    }
    public function store(object $data)
    {
        $shippingaddress                = empty($data->ship_id) || $data->action == 'create' ? new ShippingaddressModel() : ShippingaddressModel::find($data->ship_id);
        $shippingaddress->store_id      = $data->store_id;
        $shippingaddress->country_id    = $data->ship_country_id;
        $shippingaddress->state_id      = $data->ship_state_id;
        $shippingaddress->city          = $data->shipping_city;
        $shippingaddress->postcode      = $data->shipping_postcode;
        $shippingaddress->address       = $data->shipping_address;
        $shippingaddress->status        = $data->status ? $data->status : 1;
        $shippingaddress->customer_id   = $data->customer_id;
        $shippingaddress->location_link = $data->shipping_location_link;

        $shippingaddress->save();
        \DB::table('db_customers')->where('id', $data->customer_id)
        ->update(array(
            'shippingaddress_id' => $shippingaddress_id->id
        ));
        return $shippingaddress;
    }
}
