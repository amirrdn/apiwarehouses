<?php

namespace App\Services;
use App\Models\StockadjustmentitemsModel;
/**
 * Class StockadjustmentitemService.
 */
class StockadjustmentitemService
{
    public function stockadjustitemRelation()
    {
        return StockadjustmentitemsModel::query();
    }
    public function store(object $data)
    {
        $stock                          = new StockadjustmentitemsModel;

        $stock->warehouse_id            = $data->warehouse_id;
        $stock->adjustment_id           = $data->adjustment_id;
        $stock->item_id                 = $data->item_id;
        $stock->adjustment_qty          = $data->adjustment_qty;
        $stock->status                  = 1;
        $stock->description             = $data->description;

        $stock->save();

        return $stock;
    }
    public function update(object $data)
    {
        if($data->adjustment_item_id){
            $stock                          = StockadjustmentitemsModel::find($data->adjustment_item_id);
    
            $stock->warehouse_id            = $data->warehouse_id;
            $stock->adjustment_id           = $data->adjustment_id;
            $stock->item_id                 = isset($data->itemid) ? $data->itemid : $data->item_id;
            $stock->adjustment_qty          = $data->adjustment_qty;
            $stock->status                  = 1;
            $stock->description             = $data->description;
            $stock->store_id                = \Auth::user()->store_id;
    
            $stock->save();
    
            return $stock;
        }
    }
    public function getAdjustmentByitem(array $item_id)
    {
        return StockadjustmentitemsModel::whereIn('item_id', $item_id)->pluck('adjustment_id');
    }
}
