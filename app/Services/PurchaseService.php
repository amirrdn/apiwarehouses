<?php

namespace App\Services;

use App\Models\PurchaseModel;
use App\Models\PurchasepaymentModel;
use App\Models\PurchaseitemsModel;

use App\Services\PurchaseitemService;
use App\Services\ItemsService;
use App\Services\StoreService;
use App\Services\PurchasepaymentService;
use App\Services\AccounttransactionService;

use Larinfo;
use Carbon\Carbon;
/**
 * Class PurchaseService.
 */
class PurchaseService
{
    public function purchaseRelation()
    {
        $pu                         = PurchaseModel::from('db_purchase as pu');
        return $pu;
    }
    public function store(object $data)
    {
        $data['action']             = 'create';
        return $this->insertOrupdate($data);
    }
    public function update(object $data)
    {
        $data['action']             = 'update';
        return $this->insertOrupdate($data);
    }
    public function insertOrupdate(object $data)
    {
        $larinfo = Larinfo::getInfo();

        if($data->action == 'create'){
            $getstore                       = (new StoreService())->getStoreByid(\Auth::user()->store_id)->purchase_init;
            $pu                             = new PurchaseModel;
            $pu->purchase_code              = $getstore.$this->GetCode();
        }else if($data->action == 'update'){
            $pu                             = PurchaseModel::find($data->purchase_id);
        }
        $pu->reference_no                   = $data->reference_no;
        $pu->purchase_status                = $data->purchase_status;
        $pu->supplier_id                    = $data->supplier_id;
        $pu->other_charges_input            = $data->other_charges_input;
        $pu->other_charges_tax_id           = $data->other_charges_tax_id;
        $pu->other_charges_amt              = $data->other_charges_amt;
        $pu->discount_to_all_input          = $data->discount_to_all_input;
        $pu->discount_to_all_type           = $data->discount_to_all_type;
        $pu->tot_discount_to_all_amt        = $data->tot_discount_to_all_amt;
        $pu->subtotal                       = $data->subtotal;
        $pu->round_off                      = $data->round_off;
        $pu->grand_total                    = $data->grand_total;
        $pu->payment_status                 = $data->payment_status ? $data->payment_status : NULL;
        $pu->paid_amount                    = $data->paid_amount ? $data->paid_amount : NULL;
        if($data->action == 'create'){
            $pu->created_date               = Carbon::now()->format('Y-m-d');
            $pu->purchase_date              = date('Y-m-d', strtotime($data->purchase_date));
            $pu->created_time               = Carbon::now()->format('h:i:s');
            $pu->created_by                 = \Auth::user()->username;
            $pu->system_ip                  = request()->ip();
            $pu->system_name                = $larinfo['server']['software']['os'];
            $pu->count_id                   = PurchaseModel::count() + 1;
            $pu->store_id                   = \Auth::user()->store_id;
        }
        $pu->warehouse_id                   = $data->warehouse_id;
        $pu->company_id                     = $data->company_id ? $data->company_id : NULL;
        $pu->status                         = 1;
        $pu->return_bit                     = $data->return_bit ? $data->return_bit : NULL;

        $pu->save();

        if($data->action === 'update'){
            $dbpuitems                      = \DB::table('db_purchaseitems')
                                            ->where('purchase_id', $pu->id);
            $checkpuitem                    = $dbpuitems->get();
            if($checkpuitem){
                $dbpuitems->delete();
            }
        }
        $puitm  = [];
        if($data->items){
            foreach($data->items as $b){
                $puitm['purchase_id']       = $pu->id;
                $puitm['purchase_status']   = $pu->purchase_status;
                $puitm['item_id']           = $b['item_id'];
                $puitm['purchase_qty']      = $b['purchase_qty'];
                $puitm['price_per_unit']    = $b['price_per_unit'];
                $puitm['tax_id']            = $b['tax_id'];
                $puitm['tax_amt']           = $b['tax_amt'];
                $puitm['tax_type']          = $b['tax_type'];
                $puitm['discount_input']    = $b['discount_input'];
                $puitm['discount_amt']      = $b['discount_amt'];
                $puitm['unit_total_cost']   = $b['unit_total_cost'];
                $puitm['total_cost']        = $b['total_cost'];
                $puitm['discount_type']     = $b['discount_type'];
                $puitm['description']       = $b['description'];
                $puitm['store_id']          = $pu->store_id;
                
                (new PurchaseitemService())->inserOnly((object) $puitm);
                (new ItemsService())->update_items_quantity($b['item_id'], $pu->warehouse_id, $pu->store_id);
            }
        }
        $data['purchase_id']                = $pu->id;
        $data['payment']                    = $data->amount;
        $data['account_id']                 = $data->account_id;
        $data['module']                     = 'module';
        
        (new PurchasepaymentService())->insertOrupdatePayment($data);
        $this->updateBystatus($pu->id);
        return $pu;
    }
    public function updateBystatus($purchase_id)
    {
        $pupay                              = \DB::table('db_purchasepayments')
                                            ->selectRaw("COALESCE(SUM(payment),0) as payment")
                                            ->where('purchase_id', $purchase_id)
                                            ->first();
        $sum_of_payments                    = $pupay->payment;
        
        $purchase                           = PurchaseModel::where('id', $purchase_id)
                                            ->selectRaw("coalesce(grand_total,0) as total, supplier_id")
                                            ->first();
        $payble_total                       = $purchase->total;
        $payment_status                     = '';
        if($payble_total === $sum_of_payments){
			$payment_status                 = "Paid";
		}else if($sum_of_payments != 0 && ($sum_of_payments<$payble_total)){
			$payment_status                 = "Partial";
		} else if($sum_of_payments == 0){
			$payment_status                 = "Unpaid";
		}
        PurchaseModel::where('id', $purchase_id)->update(array(
            'payment_status' => $payment_status,
            'paid_amount'   => $sum_of_payments
        ));
        $purchase_due                       = PurchaseModel::where('id', $purchase_id)
                                            ->where('purchase_status', 'Received')
                                            ->selectRaw("COALESCE(SUM(grand_total),0)-COALESCE(SUM(paid_amount),0) as purchase_due")
                                            ->first();
        \DB::table('db_suppliers')->where('id', $purchase->supplier_id)->update(array(
            'purchase_due' => $purchase_due
        ));
    }
    public function delete(array $purchase_id)
    {
        $returnpo                           = \DB::table('db_purchasereturn as a')
                                            ->join('db_purchase as b', 'a.purchase_id', 'b.id')
                                            ->whereIn('purchase_id', $purchase_id)
                                            ->selectRaw("COUNT(*) AS tot_invoices,a.purchase_id,b.purchase_code")
                                            ->groupBy('purchase_id')
                                            ->get();
        if($returnpo->count() > 0){
            foreach($returnpo as $key => $b){
                return response()->json(['message' => "<br>".($key + 1).".Return Invoice Against Purchase Id:".$b->purchase_code."", 'code' => 400]);
            }
			exit();
        }
        $po                                 = PurchaseModel::whereIn('id', $purchase_id);
        $popay                              = PurchasepaymentModel::whereIn('purchase_id', $purchase_id);
        $poitems                            = PurchaseitemsModel::whereIn('purchase_id', $purchase_id);

        $itemid = array();
        if($poitems){
            foreach($poitems as $b){
                $itemid []= $b->item_id;
            }
        }

        $items                              = \DB::table('db_items')
                                            ->whereIn('id', $itemid)
                                            ->get();
        if(count($items) > 0){
            foreach($items as $b){
                (new ItemsService())->update_items_quantity($b->id, $b->warehouse_id, $b->store_id);
            }
        }
        if($po){
            $po->delete();
        }else{
            return response()->json([
                'message'   => 'success',
                'code'  => 200
            ]);
        }
        if($popay){
            $popay->delete();
        }
        $reset_accounts                     = \DB::table('ac_transactions')
                                            ->whereIn('ref_purchasepayments_id', $purchase_id)
                                            ->groupBy('debit_account_id', 'credit_account_id')
                                            ->get();
        if($reset_accounts){
            foreach($reset_accounts as $b){
                (new AccounttransactionService())->update_account_balance($b->debit_account_id);
                (new AccounttransactionService())->update_account_balance($b->credit_account_id);
            }
        }
        return response()->json(['message' => 'success', 'code' => 200]);
    }
    public function GetCode()
    {
        $code = PurchaseModel::count() + 1;
        $code++;
        return str_pad($code, 4, '0', STR_PAD_LEFT);
    }
}
