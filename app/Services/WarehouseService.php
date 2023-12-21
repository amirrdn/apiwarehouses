<?php

namespace App\Services;
use App\Models\WarehouseModel;
use App\Models\WarehouseitemModel;
/**
 * Class WarehouseService.
 */
class WarehouseService
{
    public function WarehouseRelations($leftjoin = false, $innerjoin = false)
    {
        $warehouse                  = WarehouseModel::from('db_warehouse as w');
        if($leftjoin){
            $warehouse              = $warehouse->leftjoin('db_store as st', 'w.store_id', 'st.id');
        }else if($innerjoin){
            $warehouse              = $warehouse->join('db_store as st', 'w.store_id', 'st.id');
        }
        return $warehouse;
    }
    public function total_available_qty_items_of_warehouse($warehouse_id, $store_id, $item_id = NULL)
    {
        $warehouse                      = WarehouseitemModel::query();
        if(!empty($store_id)){
            $warehouse                  = $warehouse->where('store_id', $store_id);
        }
        if(!empty($warehouse_id)){
            $warehouse                  = $warehouse->where('warehouse_id', $warehouse_id);
        }
        if(!empty($item_id)){
            $warehouse                  = $warehouse->where('item_id', $item_id);
        }
        $warehouses                     = $warehouse->get();

        if(count($warehouses) > 0){
            return $warehouses->first()->available_qty;
        }else{
            return 0;
        }
    }
    public function update_warehousewise_items_qty_by_store($store_id, $item_id, $warehouse_id = NULL)
    {
        $items                      = \DB::table('db_items')
                                    ->where('store_id', $store_id)
                                    ->whereIn('id', $item_id)
                                    ->select('id')
                                    ->get();
        $arr = [];
        if(count($items) > 0){
            foreach($items as $b){
               $arr [] = $this->update_warehousewise_items_qty($b->id, $warehouse_id, $store_id);
            }
        }
        
        return $arr;
    }
    public function update_warehousewise_items_qty($item_id,$warehouse_id,$store_id)
    {
        $warehouseitems             = \DB::table('db_warehouseitems')
                                    ->where('store_id', $store_id)
                                    ->where('warehouse_id', $warehouse_id)
                                    ->where('item_id', $item_id);
        $checkanywarehouse          = $warehouseitems->get();
        if(count($checkanywarehouse) > 0){
            // $available_qty = $this->totalQtyItemNew($item_id, $store_id, $warehouse_id);
            // if($available_qty > 0){
               
            //     foreach($checkanywarehouse as $b){
            //         \DB::table('db_warehouseitems')->where('id', $b->id)->update(array(
            //             "available_qty" => $available_qty
            //         ));
            //     }
            //     // $insert = \DB::table('db_warehouseitems')->insert($info);
            //     $warehouseitems->update($info);
            // }
            $warehouseitems->delete();
        }
        $available_qty = $this->totalQtyItemNew($item_id, 2, 2);
        if(abs($available_qty) > 0){
            $info   = array(  
                'store_id'      =>  $store_id,
                'warehouse_id'  =>  2,
                'item_id'       =>  $item_id,
                'available_qty' =>  abs($available_qty),
            );
            $insert = \DB::table('db_warehouseitems')->insert($info);
        }

        return $available_qty;
    }
    public function totalQtyItemNew($item_id, $store_id, $warehouse_id)
    {
        $purchase_qty               = \DB::table('db_purchaseitems')->join('db_purchase as pu', 'db_purchaseitems.purchase_id', 'pu.id')
                                    ->select(\DB::raw("COALESCE(SUM(db_purchaseitems.purchase_qty), 0) AS purchase_qty"))
                                    ->where('db_purchaseitems.item_id', $item_id)
                                    ->where('pu.store_id', $store_id)
                                    ->where('pu.warehouse_id', $warehouse_id)
                                    ->where('pu.purchase_status', 'Received')
                                    ->first();

        $purchase_return_qty        = \DB::table('db_purchasereturn')->join('db_purchaseitemsreturn as por', 'db_purchasereturn.id', 'por.return_id')
                                    ->select(\DB::raw("COALESCE(SUM(por.return_qty), 0) AS purchase_return_qty"))
                                    ->where('por.item_id', $item_id)
                                    ->where('db_purchasereturn.store_id', $store_id)
                                    ->where('db_purchasereturn.warehouse_id', $warehouse_id)
                                    ->first();
        $sales_qty                  = \DB::table('db_sales')->join('db_salesitems as si', 'db_sales.id', 'si.sales_id')
                                    ->select(\DB::raw("COALESCE(SUM(si.sales_qty), 0) AS sales_qty"))
                                    ->where('si.item_id', $item_id)
                                    ->where('db_sales.store_id', $store_id)
                                    ->where('db_sales.warehouse_id', $warehouse_id)
                                    ->first();

        $sales_return_qty           = \DB::table('db_salesreturn')->join('db_salesitemsreturn as sr', 'db_salesreturn.id', 'sr.return_id')
                                    ->select(\DB::raw("COALESCE(SUM(sr.return_qty), 0) AS sales_return_qty"))
                                    ->where('sr.item_id', $item_id)
                                    ->where('db_salesreturn.store_id', $store_id)
                                    ->where('db_salesreturn.warehouse_id', $warehouse_id)
                                    ->first();

        $stock_entry_qty            = \DB::table('db_stockadjustmentitems')->select(\DB::raw("COALESCE(SUM(adjustment_qty),0) AS adjustment_qty"))
                                    ->where('store_id', $store_id)
                                    ->where('warehouse_id', $warehouse_id)
                                    ->where('item_id', $item_id)
                                    ->first();

        $stocktransfer_qty_add      = \DB::table('db_stocktransferitems')->select(\DB::raw("COALESCE(SUM(transfer_qty),0) AS stocktransfer_qty"))
                                    ->where('store_id', $store_id)
                                    ->where('warehouse_to', $warehouse_id)
                                    ->where('item_id', $item_id)
                                    ->first();
        $stocktransfer_qty_deduct   = \DB::table('db_stocktransferitems')->select(\DB::raw("COALESCE(SUM(transfer_qty),0) AS stocktransfer_qty"))
                                    ->where('store_id', $store_id)
                                    ->where('warehouse_from', $warehouse_id)
                                    ->where('item_id', $item_id)
                                    ->first();

        return ($stock_entry_qty->adjustment_qty + $purchase_qty->purchase_qty + $stocktransfer_qty_add->stocktransfer_qty - $stocktransfer_qty_deduct->stocktransfer_qty + $sales_return_qty->sales_return_qty - $purchase_return_qty->purchase_return_qty)-$sales_qty->sales_qty;
    }
}
