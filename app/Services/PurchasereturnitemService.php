<?php

namespace App\Services;

use App\Models\PurchasereturnitemModel;
/**
 * Class PurchasereturnitemService.
 */
class PurchasereturnitemService
{
    public function poReturnsql()
    {
        return PurchasereturnitemModel::from('db_purchaseitemsreturn as payitem');
    }
    public function insertData(object $data)
    {
        $itemreturn                     = new PurchasereturnitemModel;

        $itemreturn->store_id           = $data->store_id;
        $itemreturn->purchase_id        = $data->purchase_id;
        $itemreturn->return_id          = $data->return_id;
        $itemreturn->return_status      = $data->return_status;
        $itemreturn->item_id            = $data->item_id;
        $itemreturn->return_qty         = $data->return_qty;
        $itemreturn->price_per_unit     = $data->price_per_unit;
        $itemreturn->tax_id             = $data->tax_id;
        $itemreturn->tax_amt            = $data->tax_amt;
        $itemreturn->tax_type           = $data->tax_type;
        $itemreturn->discount_input     = $data->discount_input;
        $itemreturn->discount_amt       = $data->discount_amt;
        $itemreturn->discount_type      = $data->discount_type;
        $itemreturn->unit_total_cost    = $data->unit_total_cost;
        $itemreturn->total_cost         = $data->total_cost;
        $itemreturn->profit_margin_per  = $data->profit_margin_per;
        $itemreturn->unit_sales_price   = $data->unit_sales_price;
        $itemreturn->status             = $data->status;
        $itemreturn->description        = $data->description;

        $itemreturn->save();

        return $itemreturn;
    }
}
