<?php

namespace App\Services;
use Illuminate\Http\Request;

use App\Models\ItemsModel;
use App\Services\StoreService;
use App\Services\StockadjustmentService;
use App\Services\StockadjustmentitemService;
use App\Services\CustomService;
use App\Services\WarehouseService;
Use Exception;
use Larinfo;
use Carbon\Carbon;
/**
 * Class ItemsService.
 */
class ItemsService
{
    public function __construct(){
        $larinfo                        = Larinfo::getInfo();
        $this->date_now                 = Carbon::now()->format('Y-m-d');
        $this->ip                       = request()->ip();
        $this->info                     = $larinfo['server']['software']['os'];
        $this->timenow                  = Carbon::now()->format('h:i:s');
    }
    public function itemsRelation()
    {
        $items                          = ItemsModel::from('db_items as itm')
                                        ->join('db_category as c', 'itm.category_id', 'c.id')
                                        ->leftjoin('db_store as st', 'itm.store_id', 'st.id')
                                        ->leftjoin('db_units as u', 'itm.unit_id', 'u.id')
                                        ->join('db_tax as tx', 'itm.tax_id', 'tx.id')
                                        ->join('db_users as us', 'itm.created_by', 'us.username')
                                        ->leftjoin('db_brands as br', 'itm.brand_id', 'br.id')
                                        ->leftjoin('db_warehouse as w', 'st.id', 'w.store_id')
                                        ->leftjoin('db_stockadjustmentitems as aj', 'itm.id', 'aj.item_id');
        return $items;
    }
    public function store(object $data)
    {
        try{
            $itemcode                   = $this->GetCodeItem();
            $codeitems                  = (new StoreService())->getStoreByid(\Auth::user()->store_id)->item_init.$itemcode;
            $items                      = new ItemsModel;

            $items->store_id            = \Auth::user()->store_id;
            $items->count_id            = ItemsModel::count() + 1;
            $items->item_code           = $codeitems;
            $items->item_name           = $data->item_name;
            $items->category_id         = $data->category_id;
            $items->sku                 = $data->sku;
            $items->hsn                 = $data->hsn;
            $items->unit_id             = $data->unit_id;
            $items->alert_qty           = $data->alert_qty;
            $items->brand_id            = $data->brand_id;
            $items->lot_number          = $data->lot_number;
            $items->expire_date         = $data->expire_date;
            $items->price               = $data->price;
            $items->tax_id              = $data->tax_id;
            $items->purchase_price      = $data->purchase_price;
            $items->tax_type            = $data->tax_type;
            $items->profit_margin       = $data->profit_margin;
            $items->sales_price         = $data->sales_price;
            $items->stock               = $data->stock;
            $items->item_image          = $data->item_image;
            $items->system_ip           = $this->ip;
            $items->system_name         = $this->info;
            $items->created_date        = $this->date_now;
            $items->created_time        = $this->timenow;
            $items->created_by          = \Auth::user()->username;
            $items->company_id          = $data->company_id;
            $items->status              = !empty($data->status) ? $data->status : 1;
            $items->discount_type       = $data->discount_type;
            $items->discount            = $data->discount;
            $items->service_bit         = $data->service_bit;
            $items->seller_points       = $data->seller_points;
            $items->custom_barcode      = $data->custom_barcode;
            $items->description         = $data->description;
            $items->item_group          = 'Single';
            $items->parent_id           = $data->parent_id;
            $items->variant_id          = $data->variant_id;
            $items->child_bit           = $data->child_bit;
            $items->mrp                 = $data->mrp;
            
            $items->save();
            if($data->stock > 0){
                $data['item_id'] = $items->id;
                (new StockadjustmentService())->store($data);
            }
            return response()->json([
                'message'   => 'success insert',
                'code'  => 200,
                'data'  => $items
            ]);
        } catch(Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
    public function update(object $data)
    {
        try{
            $items                      = ItemsModel::find($data->item_id);

            $items->store_id            = $items->store_id;
            $items->count_id            = $items->count_id;
            $items->item_code           = $items->item_code;
            $items->item_name           = $data->item_name;
            $items->category_id         = $data->category_id;
            $items->sku                 = $data->sku;
            $items->hsn                 = $data->hsn;
            $items->unit_id             = $data->unit_id;
            $items->alert_qty           = $data->alert_qty;
            $items->brand_id            = $data->brand_id;
            $items->lot_number          = $data->lot_number;
            $items->expire_date         = $data->expire_date;
            $items->price               = $data->price;
            $items->tax_id              = $data->tax_id;
            $items->purchase_price      = $data->purchase_price;
            $items->tax_type            = $data->tax_type;
            $items->profit_margin       = $data->profit_margin;
            $items->sales_price         = $data->sales_price;
            $items->stock               = $data->stock;
            $items->item_image          = $data->item_image;
            $items->system_ip           = $this->ip;
            $items->system_name         = $this->info;
            $items->created_date        = $this->date_now;
            $items->created_time        = $this->timenow;
            $items->created_by          = $items->created_by;
            $items->company_id          = $data->company_id;
            $items->status              = !empty($data->status) ? $data->status : 1;
            $items->discount_type       = $data->discount_type;
            $items->discount            = $data->discount;
            $items->service_bit         = $data->service_bit;
            $items->seller_points       = $data->seller_points;
            $items->custom_barcode      = $data->custom_barcode;
            $items->description         = $data->description;
            $items->item_group          = 'Single';
            $items->parent_id           = $data->parent_id;
            $items->variant_id          = $data->variant_id;
            $items->child_bit           = $data->child_bit;
            $items->mrp                 = $data->mrp;
            
            $items->save();
            if($data->stock > 0){
                (new StockadjustmentService())->update($data);
            }
            return response()->json([
                'message'   => 'success insert',
                'code'  => 200,
                'data'  => $items
            ]);
        } catch(Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
    public function delete($data)
    {
        $dbitems                = ItemsModel::whereIn('id', $data->item_id);
        $getadjustment          = (new StockadjustmentitemService())->getAdjustmentByitem($data->item_id);
        (new StockadjustmentService())->delete($getadjustment);
        if(count($dbitems->get()) > 0){
            $dbitems->delete();
            return response()->json([
                'message'   => 'success delete',
                'code'  => 200
            ]);
        }else{
            return response()->json([
                'message'   => 'error delete',
                'code'  => 400
            ]);
        }
    }
    public function GetCodeItem()
    {
        $itemscode = ItemsModel::count();
        $itemscode++;
        return str_pad($itemscode, 4, '0', STR_PAD_LEFT);
    }
    public function get_item_details($item_id, $warehouse_id)
    {
        $items              = $this->itemsRelation()
                            ->where('itm.status', 1)
                            ->where('itm.id', $item_id)
                            ->select('tx.id as tax_id', 'itm.*', 'tx.tax', 'tx.tax_name')
                            ->first();

        $item_tax_amt       = ($items->tax_type == 'Inclusive') ? (new CustomService())->calculate_inclusive($items->sales_price,$items->tax) : (new Custom())->calculate_exclusive($items->sales_price,$items->tax);
        $warehouse_stock    = (new WarehouseService())->total_available_qty_items_of_warehouse($warehouse_id,null,$item_id);

        $item_array = [
            'id' 					=> $items->id,
            'item_name' 			=> $items->item_name,
            'stock' 				=> $warehouse_stock,
            'sales_price' 			=> $items->sales_price,
            'purchase_price' 		=> $items->purchase_price,
            'tax_id' 				=> $items->tax_id,
            'tax_type' 				=> $items->tax_type,
            'tax' 					=> $items->tax,
            'tax_name' 				=> $items->tax_name,
            'item_tax_amt' 			=> $item_tax_amt,
            'discount_type' 		=> $items->discount_type,
            'discount' 				=> $items->discount,
            'service_bit' 			=> $items->service_bit,
            'price'                 => $items->price,
            'seller_points'         => $items->seller_points
        ];
        return $item_array;
    }
    public function update_items_quantity($item_id, $warehouse_id, $store_id)
    {
        $dbitems                    = ItemsModel::where('id', $item_id)->selectRaw("service_bit")->first();
        
        $dbstock                    = \DB::table('db_stockadjustmentitems')
                                    ->where('item_id', $item_id)
                                    ->selectRaw("COALESCE(SUM(adjustment_qty),0) as stock_qty")
                                    ->first();

        $stock_qty                  = $dbstock ? $dbstock->stock_qty : 0;

        $dbpuqty                    = \DB::table('db_purchaseitems')
                                    ->where('item_id', $item_id)
                                    ->selectRaw("COALESCE(SUM(purchase_qty),0) as pu_tot_qty")
                                    ->first();

        $pu_tot_qty                 = $dbpuqty ? $dbpuqty->pu_tot_qty : 0;
        
        $dbsaleitem                 = \DB::table('db_salesitems')
                                    ->where('item_id', $item_id)
                                    ->where('sales_status', 'Final')
                                    ->selectRaw("coalesce(SUM(sales_qty),0) as sl_tot_qty")
                                    ->first();
        $sl_tot_qty                 = $dbsaleitem ? $dbsaleitem->sl_tot_qty : 0;

        $dbpureturn                 = \DB::table('db_purchaseitemsreturn')
                                    ->where('item_id', $item_id)
                                    ->selectRaw("COALESCE(SUM(return_qty),0) as pu_return_tot_qty")
                                    ->first();
        $pu_return_tot_qty          = $dbpureturn ? $dbpureturn->pu_return_tot_qty : 0;

        $dbsaleitemreturn           = \DB::table('db_salesitemsreturn')
                                    ->where('item_id', $item_id)
                                    ->selectRaw("COALESCE(SUM(return_qty),0) as sl_return_tot_qty")
                                    ->first();
        $sl_return_tot_qty          = $dbsaleitemreturn ? $dbsaleitemreturn->sl_return_tot_qty : 0;

        $stock  = ((($stock_qty+$pu_tot_qty) - $sl_tot_qty) + $sl_return_tot_qty) - $pu_return_tot_qty;
        
        $update = ItemsModel::where('id', $item_id)->update(array(
            'stock' => $stock
        ));
        $data = (new WarehouseService())->update_warehousewise_items_qty_by_store($store_id,[$item_id],$warehouse_id);
        return $data;
    }
    public function get_seller_points($item_id)
    {
        $selerpoint         = ItemsModel::where('id', $item_id)->select('seller_points')->first();

        if($selerpoint){
            return $selerpoint->seller_points;
        }else{
            return 0;
        }
    }
}
