<?php

namespace App\Services;
use App\Models\StockadjustmentModel;
use App\Models\StockadjustmentitemsModel;

use App\Services\StockadjustmentitemService;
use App\Services\WarehouseService;
use Carbon\Carbon;
use Larinfo;
/**
 * Class StockadjustmentService.
 */
class StockadjustmentService
{
    public function __construct(){
        $larinfo                        = Larinfo::getInfo();
        $this->date_now                 = Carbon::now()->format('Y-m-d');
        $this->ip                       = request()->ip();
        $this->info                     = $larinfo['server']['software']['os'];
        $this->timenow                  = Carbon::now()->format('h:i:s');
    }
    public function stockadjustRelations($leftjoin = false, $join = false)
    {
        $stockadjust                    = StockadjustmentModel::from('db_stockadjustment as sa');
        if($leftjoin){
            $stockadjust                = $stockadjust->leftjoin('db_store as st', 'sa.store_id', 'st.id');
        }else if($join){
            $stockadjust                = $stockadjust->join('db_store as st', 'sa.store_id', 'st.id');
        }
        return $stockadjust;
    }
    public function store(object $data)
    {
        $stock                      = new StockadjustmentModel;
    
        $stock->store_id            = \Auth::user()->store_id;
        $stock->adjustment_date     = Carbon::now()->format('Y-m-d');
        $stock->adjustment_note     = $data->adjustment_note;
        $stock->created_date        = Carbon::now()->format('Y-m-d');
        $stock->created_time        = Carbon::now()->format('h:i:s');
        $stock->created_by          = \Auth::user()->username;
        $stock->system_ip           = $this->ip;
        $stock->system_name         = $this->info;
        $stock->status              = 1;
        $stock->warehouse_id        = $data->warehouse_id;

        $stock->save();
        $data['adjustment_id']      = $stock->id;

        (new StockadjustmentitemService())->store($data);
        return $stock;
    }
    
    public function update(object $data)
    {
        if($data->adjust_id){
            $stock                      = StockadjustmentModel::find($data->adjust_id);
        
            $stock->store_id            = \Auth::user()->store_id;
            $stock->adjustment_date     = Carbon::now()->format('Y-m-d');
            $stock->adjustment_note     = $data->adjustment_note;
            $stock->created_date        = Carbon::now()->format('Y-m-d');
            $stock->created_time        = Carbon::now()->format('h:i:s');
            $stock->created_by          = \Auth::user()->username;
            $stock->system_ip           = $this->ip;
            $stock->system_name         = $this->info;
            $stock->status              = 1;
            $stock->warehouse_id        = $data->warehouse_id;
    
            $stock->save();
            $data['adjustment_id']      = $stock->id;
            (new StockadjustmentitemService())->update($data);
            return $stock;
        }
    }
    public function delete($id)
    {
        $dbstock                        = StockadjustmentModel::whereIn('id', $id);
        if(count($dbstock->get()) > 0){
            $dbstock->delete();
        }
    }
    public function deleteMaster(array $adjust_id)
    {
        $prev_item_ids                  = StockadjustmentitemsModel::whereIn('adjustment_id', $adjust_id);

        $stocks                         = StockadjustmentModel::whereIn('id', $adjust_id);
        if(count($stocks->get())){
            $stocks->delete();
        }else{
            return response()->json([
                'message'   => 'errorr delete !',
                'code'  => 400
            ]);
        }
        if(count($prev_item_ids->get()) > 0){
            foreach($prev_item_ids->get() as $b){
                $classitem->update_items_quantity($b->item_id);
            }
            (new WarehouseService())->update_warehousewise_items_qty_by_store($request,'', $prev_item_ids->pluck('item_id') );
            $prev_item_ids->delete();
        }
        return response()->json([
            'message'   => 'success delete',
            'code'  => 200
        ]);
    }
}
