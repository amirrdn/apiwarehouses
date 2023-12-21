<?php

namespace App\Services;
use App\Models\StoreModel;
/**
 * Class StoreService.
 */
class StoreService
{
    public function getStoreByid($store_id)
    {
        return StoreModel::find($store_id);
    }
    public function get_count_id($table)
    {
        return \DB::table($table)->where('store_id', \Auth::user()->store_id)
        ->selectRaw("coalesce(max(count_id),0)+1 as count_id")
        ->first();
    }
}
