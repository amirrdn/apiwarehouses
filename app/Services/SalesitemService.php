<?php

namespace App\Services;
use App\Models\SalesitemsModel;
/**
 * Class SalesitemService.
 */
class SalesitemService
{
    public function SalesitemRelations()
    {
        return SalesitemsModel::from('db_salesitems as sitm')->join('db_tax as tx', 'sitm.tax_id', 'tx.id')
        ->join('db_items as itm', 'sitm.item_id', 'itm.id')
        ->join('db_units as un', 'itm.unit_id', 'un.id');
    }
    public function store(object $data){
        return $this->insertOrupdate((object) $data);
    }
    public function insertOrupdate(object $data)
    {
        if($data->action == 'create'){
            $itemsales                  = new SalesitemsModel;
        }else if($data->action == 'update'){
            $itemsales                  = SalesitemsModel::find($data->sales_item_id);
        }
        $itemsales->store_id            = $data->store_id;
        // $itemsales                      = $data->action == 'create' ? new SalesitemsModel : SalesitemsModel::find($data->sales_item_id);
        
        $itemsales->sales_id            = $data->sales_id;
        $itemsales->sales_status        = $data->sales_status;
        $itemsales->item_id             = $data->item_id;
        $itemsales->description         = $data->description;
        $itemsales->sales_qty           = $data->sales_qty;
        $itemsales->price_per_unit      = $data->price_per_unit;
        $itemsales->tax_type            = $data->tax_type;
        $itemsales->tax_id              = $data->tax_id;
        $itemsales->tax_amt             = $data->tax_amt;
        $itemsales->discount_type       = $data->discount_type;
        $itemsales->discount_input      = $data->discount_input;
        $itemsales->discount_amt        = $data->discount_amt;
        $itemsales->unit_total_cost     = $data->unit_total_cost;
        $itemsales->total_cost          = $data->total_cost;
        $itemsales->status              = 1;
        $itemsales->seller_points       = $data->seller_points;
        $itemsales->purchase_price      = $data->purchase_price;

        $itemsales->save();
        return $itemsales;
    }
}
