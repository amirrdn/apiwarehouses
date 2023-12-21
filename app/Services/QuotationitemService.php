<?php

namespace App\Services;

use App\Models\QuotationitemsModel;
/**
 * Class QuotationitemService.
 */
class QuotationitemService
{
    public function quotationRelation()
    {
        $quotitem                       = QuotationitemsModel::from('db_quotationitems as qitm')
                                        ->join('db_tax as tx', 'qitm.tax_id', 'tx.id')
                                        ->join('db_items as itm', 'qitm.item_id', 'itm.id')
                                        ->join('db_units as un', 'itm.unit_id', 'un.id');
        return $quotitem;
    }
    public function insertOrupdate(object $data)
    {
        if($data->action == 'create'){
            $quotitem                   = new QuotationitemsModel;
            $quotitem->store_id         = \Auth::user()->store_id;
        }else if($data->action == 'update'){
            $quotitem                   = QuotationitemsModel::find($data->quotation_item_id);
        }else{
            return false;
        }
        $quotitem->quotation_id         = $data->quotation_id;
        $quotitem->quotation_status     = $data->quotation_status;
        $quotitem->item_id              = $data->item_id;
        $quotitem->description          = $data->description;
        $quotitem->quotation_qty        = $data->quotation_qty;
        $quotitem->price_per_unit       = $data->price_per_unit;
        $quotitem->tax_type             = $data->tax_type;
        $quotitem->tax_id               = $data->tax_id;
        $quotitem->tax_amt              = $data->tax_amt;
        $quotitem->discount_type        = $data->discount_type;
        $quotitem->discount_input       = $data->discount_input;
        $quotitem->discount_amt         = $data->discount_amt;
        $quotitem->unit_total_cost      = $data->unit_total_cost;
        $quotitem->total_cost           = $data->total_cost;
        $quotitem->status               = 1;
        $quotitem->seller_points        = $data->seller_points;

        $quotitem->save();

        return $quotitem;
    }
}
