<?php

namespace App\Services;

use App\Models\PurchasereturnModel;
use App\Services\StoreService;
use App\Services\PurchasereturnitemService;
use App\Services\ItemsService;
use App\Services\PurchasepaymentService;
use App\Services\AccounttransactionService;
use App\Services\PurchaseReturnPaymentService;
/**
 * Class PurchasereturnService.
 */
use Larinfo;
use Carbon\Carbon;

class PurchasereturnService
{
    public function purchaseSql()
    {
        return PurchasereturnModel::from('db_purchasereturn as pur')->leftjoin('db_purchasepaymentsreturn as payret', 'pur.id', 'payret.return_id');
    }
    public function store(object $data)
    {
        $data['action']                     = 'create';
        return $this->insertOrupdate($data);
    }
    public function update(object $data)
    {
        $data['action']                     = 'update';
        return $this->insertOrupdate((object) $data);
    }
    public function insertOrupdate(object $data)
    {
        $larinfo = Larinfo::getInfo();

        if($data->action == 'create'){
            $getstore                       = (new StoreService())->getStoreByid(\Auth::user()->store_id)->purchase_return_init;
            $code                           = $this->GetCode();
            $purchase_return_code           = $getstore.$code;
            $return                         = new PurchasereturnModel;
            $return->return_date            = $data->return_date ? date('Y-m-d', strtotime($data->return_date)) : date('Y-m-d');
            $return->store_id               = \Auth::user()->store_id;
            $return->count_id               = PurchasereturnModel::count() + 1;
            $return->purchase_id            = $data->purchase_id;
            $return->return_code            = $purchase_return_code;
        }else if($data->action == 'update'){
            $return                         = PurchasereturnModel::find($data->purchase_return_id);
        }
        $return->reference_no               = isset($data->reference_no) ? $data->reference_no : '';
        $return->warehouse_id               = $data->warehouse_id;
        
        $return->return_status              = $data->return_status;
        $return->supplier_id                = $data->supplier_id;
        $return->other_charges_input        = $data->other_charges_input;
        $return->other_charges_tax_id       = $data->other_charges_tax_id;
        $return->other_charges_amt          = $data->other_charges_amt;
        $return->discount_to_all_input      = $data->discount_to_all_input;
        $return->discount_to_all_type       = $data->discount_to_all_type;
        $return->tot_discount_to_all_amt    = $data->tot_discount_to_all_amt;
        $return->subtotal                   = $data->subtotal;
        $return->round_off                  = $data->round_off;
        $return->grand_total                = $data->grand_total;
        $return->return_note                = $data->return_note;
        $return->payment_status             = $data->payment_status;
        $return->paid_amount                = $data->paid_amount;
        if($data->action == 'create'){
            $return->created_date           = Carbon::now()->format('Y-m-d');
            $return->created_time           = Carbon::now()->format('h:i:s');
            $return->created_by             = \Auth::user()->username;
            $return->system_ip              = request()->ip();
            $return->system_name            = $larinfo['server']['software']['os'];
        }
        $return->status                     = 1;

        $return->save();

        if($data->action == 'update'){
            $checkitemreturn                = \DB::table('db_purchaseitemsreturn')->where('purchase_id', $return->purchase_id)->where('return_id', $return->id)->get();
  
            if($checkitemreturn){
                \DB::table('db_purchaseitemsreturn')->where('purchase_id', $return->purchase_id)->where('return_id', $return->id)->delete();
            }
        }
            foreach($data->items as $b){
                $data['purchase_id']        = $return->purchase_id;
                $data['store_id']           = \Auth::user()->store_id;
                $data['return_id']          = $return->id;
                $data['return_status']      = $data->return_status;
                $data['item_id']            = $b['item_id'];
                $data['return_qty'] 		= $b['return_qty'];
                $data['price_per_unit'] 	= $b['price_per_unit'];
                $data['tax_id'] 			= $b['tax_id'];
                $data['tax_amt'] 			= $b['tax_amt'];
                $data['discount_input'] 	= $b['discount_input'];
                $data['discount_type'] 	    = $b['discount_type'];
                $data['discount_amt'] 		= $b['discount_amt'];
                $data['unit_total_cost'] 	= $b['unit_total_cost'];
                $data['total_cost'] 		= $b['total_cost'];
                $data['status']			    = 1;
                $data['description']		= $b['description'];
                $data['tax_type']		    = $b['tax_type'];

                (new PurchasereturnitemService())->insertData($data);
                (new ItemsService())->update_items_quantity($b['item_id'], $return->warehouse_id, $return->store_id);
            }
        $data['return_id']                  = $return->id;
        $data['payment_date']               = $return->return_date;
        $data['payment_date']               = $return->return_date;
        $prev_item_ids    = \DB::table('db_purchaseitemsreturn')->where('return_id', $return->id)->pluck('item_id')->toArray(); 
        $data['payment']                    = $data->amount;
        $data['payment_note']               = $return->return_note;
        $data['prev_item_ids']              = $prev_item_ids;
        $data['purchase_id']                = $return->purchase_id;
        if($data->action == 'update'){
            $dbpayments                     = \DB::table('db_purchasepaymentsreturn')->where('return_id', $return->id);
            if(count($dbpayments->get()) > 0){
                $dbpayments->delete();
            }
        }
        $data['store_id']                   = $return->store_id;
        $data['purchase_id']                = $return->purchase_id;
        $data['return_id']                  = $return->id;
        $data['amount']                     = $data->amount;
        (new PurchaseReturnPaymentService())->store($data);
        (new AccounttransactionService())->insert_account_transaction($data);
        
        return $return;
    }
    public function GetCode()
    {
        $code = PurchasereturnModel::count();
        $code++;
        return str_pad($code, 4, '0', STR_PAD_LEFT);
    }
}
