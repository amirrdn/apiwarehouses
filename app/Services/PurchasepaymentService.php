<?php

namespace App\Services;

use App\Models\PurchasepaymentreturnModel;
use App\Models\PurchasepaymentModel;

use App\Services\StoreService;
use App\Services\AccounttransactionService;

use Larinfo;
use Carbon\Carbon;
/**
 * Class PurchasepaymentService.
 */
class PurchasepaymentService
{
    public function sqlPayment()
    {
        return PurchasepaymentModel::from('db_purchasepayments as py');
    }
    public function update_purchase_payment_status_by_purchase_id($return_id)
    {
        $pupayreturn                        = PurchasepaymentreturnModel::where('return_id', $return_id)
                                            ->selectRaw("COALESCE(SUM(payment),0) as payment")
                                            ->first();
        $sum_of_payments                    = $pupayreturn ? $pupayreturn->payment : 0;

        $pureturns                          = \DB::table('db_purchasereturn')
                                            ->where('id', $return_id)
                                            ->selectRaw("coalesce(grand_total,0) as total")
                                            ->first();
        $payble_total                       = $pureturns ? $pureturns->total : 0;

        $pending_amt                        = $payble_total - $sum_of_payments;

        $payment_status='';
		if($payble_total == $sum_of_payments){
			$payment_status = "Paid";
		} else if($sum_of_payments !== 0 && ($sum_of_payments < $payble_total)){
			$payment_status = "Partial";
		} else if($sum_of_payments == 0){
			$payment_status = "Unpaid";
		}
        
        \DB::table('db_purchasereturn')->where('id', $return_id)
        ->update([
            'payment_status'    => $payment_status,
            'paid_amount'   => $sum_of_payments
        ]);

        $puretrunssupplier                  = \DB::table('db_purchasereturn')
                                            ->where('id', $return_id)
                                            ->select('supplier_id')
                                            ->first();
        //update db supplier
        $pudue                              = \DB::table('db_purchasereturn')
                                            ->selectRaw("COALESCE(SUM(grand_total),0)- COALESCE(SUM(paid_amount),0) as purchase_return_due ")
                                            ->where('supplier_id', $puretrunssupplier->supplier_id)
                                            ->first();
        \DB::table('db_suppliers')->where('id', $puretrunssupplier->supplier_id)
        ->update([
            'purchase_return_due' => $pudue->purchase_return_due
        ]);
    }
    public function insertOrupdatePayment(object $data)
    {
        $larinfo = Larinfo::getInfo();
        if($data->action === 'create' || empty($data->payment_id)){
            $payments               = new PurchasepaymentModel;
            $payments->count_id     = PurchasepaymentModel::count() + 1;
        }else{
            $payments               = PurchasepaymentModel::find($data->payment_id);
        }
        if($data->action === 'create' || empty($data->payment_id)){
            $getstore               = (new StoreService())->getStoreByid(\Auth::user()->store_id)->purchase_payment_init;
            $paycode                = $this->setCodepay();
            $payments->payment_code = $getstore.$paycode;
        }
        $payments->store_id         = \Auth::user()->store_id;
        $payments->purchase_id      = $data->purchase_id;
        if($data->action === 'create' || empty($data->payment_id)){
            $payments->payment_date = isset($data->payment_date) ? date('Y-m-d', strtotime($data->payment_date)) : date('Y-m-d');
        }
        $payments->payment_type     = $data->payment_type;
        $payments->payment          = $data->payment ? $data->payment : $data->amount;
        $payments->payment_note     = $data->payment_note;
        if($data->action === 'create' || empty($data->payment_id)){
            $payments->created_date   = Carbon::now()->format('Y-m-d');
            $payments->created_time   = Carbon::now()->format('h:i:s');
            $payments->system_ip      = request()->ip();
            $payments->system_name    = $larinfo['server']['software']['os'];
            $payments->created_by     = \Auth::user()->username;
        }
        $payments->status               = 1;
        $payments->account_id           = $data->account_id;
        $payments->supplier_id          = $data->supplier_id;
        $payments->short_code           = $data->short_code;

        $payments->save();

        if(!empty($data->account_id)){
            if($data->module == 'module'){
                $data['reference_table_id']     = $payments->id;
                (new AccounttransactionService())->insert_account_transaction($data);
            }else{
                $data['transaction_type']       = 'PURCHASE PAYMENT';
                $data['reference_table_id']     = $payments->id;
                $data['debit_account_id']       = $data->account_id;
                $data['credit_account_id']      = null;
                $data['debit_amt']              = $data->amount ? $data->amount : $data->payment;
                $data['credit_amt']             = 0;
                $data['process']                = 'SAVE';
                $data['note']                   = $data->payment_note;
                $data['transaction_date']       = $payments->created_date;
                $data['payment_code']           = $payments->payment_code;
                $data['customer_id']            = null;
                $data['supplier_id']            = $data->supplier_id;

            }
            (new AccounttransactionService())->insert_account_transaction($data);
        }
        return $payments;
    }
    public function setCodepay()
    {
        $code = (new StoreService())->get_count_id('db_purchasepayments')->count_id - 1;
        $code++;
        return str_pad($code, 4, '0', STR_PAD_LEFT);
    }
}
