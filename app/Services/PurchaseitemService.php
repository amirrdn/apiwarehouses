<?php

namespace App\Services;

use App\Models\PurchaseitemsModel;
/**
 * Class PurchaseitemService.
 */
class PurchaseitemService
{
    public function sqlPurchaseitem()
    {
        return PurchaseitemsModel::from('db_purchaseitems as puitem');
    }
    public function inserOnly(object $data)
    {
        $itempu                         = new PurchaseitemsModel;
        $itempu->purchase_id            = $data->purchase_id;
        $itempu->purchase_status        = $data->purchase_status;
        $itempu->item_id                = $data->item_id;
        $itempu->purchase_qty           = $data->purchase_qty;
        $itempu->price_per_unit         = $data->price_per_unit;
        $itempu->tax_type               = $data->tax_type;
        $itempu->tax_id                 = $data->tax_id;
        $itempu->tax_amt                = $data->tax_amt;
        $itempu->discount_type          = $data->discount_type;
        $itempu->discount_input         = $data->discount_input;
        $itempu->discount_amt           = $data->discount_amt;
        $itempu->unit_total_cost        = $data->unit_total_cost;
        $itempu->total_cost             = $data->total_cost;
        $itempu->profit_margin_per      = isset($data->profit_margin_per) ? $data->profit_margin_per : null;
        $itempu->unit_sales_price       = isset($data->unit_sales_price) ? $data->unit_sales_price : null;
        $itempu->status                 = 1;
        $itempu->description            = $data->description;
        $itempu->store_id               = $data->store_id;

        $itempu->save();

        return $itempu;
    }
}
