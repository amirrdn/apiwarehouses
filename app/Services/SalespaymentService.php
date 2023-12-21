<?php

namespace App\Services;
use App\Models\SalespaymentModel;

use App\Services\StoreService;
use App\Services\CustomerService;
use App\Services\AccounttransactionService;
use Larinfo;
use Carbon\Carbon;
/**
 * Class SalespaymentService.
 */
class SalespaymentService
{
    public function __construct()
    {
        $larinfo                        = Larinfo::getInfo();
        $this->date_now                 = Carbon::now()->format('Y-m-d');
        $this->ip                       = request()->ip();
        $this->info                     = $larinfo['server']['software']['os'];
        $this->timenow                  = Carbon::now()->format('h:i:s');
    }
    public function salesPaymentRelations($leftjoin = false, $innerjoin = false)
    {
        $payment                        = SalespaymentModel::from('db_salespayments as py')
                                        ->join('db_sales as s', 'py.sales_id', 's.id')
                                        ->leftjoin('db_customers as cs', 'py.customer_id', 'cs.id')
                                        ->leftjoin('ac_accounts as ac', 'py.account_id', 'ac.id');
        if($leftjoin){
            $payment                    = $payment->leftjoin('db_store as st', 'py.store_id', 'st.id');
        }else if($innerjoin){
            $payment                    = $payment->join('db_store as st', 'py.store_id', 'st.id');
        }

        return $payment;
    }
    public function store(object $data)
    {
        $larinfo                        = Larinfo::getInfo();
        
        $salespay                   = $data->action == 'create' ? new SalespaymentModel : SalespaymentModel::find($data->sales_payment_id);
        $salespay->count_id         = SalespaymentModel::max('count_id') + 1;
        if(!empty($data->payment_code)){
            $salespay->payment_code = $data->payment_code;
        }else{
            $stores                 = (new StoreService())->getStoreByid(\Auth::user()->store_id)
                                    ->select('db_store.*')
                                    ->first();
            $salespay->payment_code = $stores->sales_payment_init.$this->getCode();
        }
        $salespay->store_id         = !empty($data->store_id) ? $data->store_id : \Auth::user()->store_id;
        $salespay->created_time     = Carbon::now()->format('h:i:s');
        $salespay->created_by       = \Auth::user()->username;
        $salespay->system_ip        = request()->ip();
        $salespay->system_name      = $larinfo['server']['software']['os'];
        $salespay->created_date     = Carbon::now()->format('Y-m-d');
        $salespay->sales_id             = $data->sales_id;
        $salespay->payment_date         = !empty($data->payment_date) ? date('Y-m-d', strtotime($data->payment_date)) : date('Y-m-d');
        $salespay->payment_type         = $data->payment_type;
        $salespay->payment              = $data->payment;
        $salespay->payment_note         = $data->payment_note;
        $salespay->change_return        = isset($data->change_return) ? $data->change_return : '';
        $salespay->status               = 1;
        $salespay->account_id           = $data->account_id;
        $salespay->customer_id          = $data->customer_id;
        $salespay->short_code           = isset($data->short_code) ? $data->short_code : '';
        $salespay->advance_adjusted     = $data->advance_adjusted ? $data->advance_adjusted : '';
        $salespay->cheque_number        = $data->cheque_number ? $data->cheque_number : '';
        $salespay->cheque_period        = isset($data->cheque_period) ? $data->cheque_period : '';
        $salespay->cheque_status        = isset($data->cheque_status) ? $data->cheque_status : '';

        $salespay->save();
        $this->updatePaymentsalesstatus($data);
        (new CustomerService())->set_customer_tot_advance($salespay->customer_id);
        return $salespay;
    }
    public function updatePaymentsalesstatus(object $data)
    {
        $dbpaymentsales                     = SalespaymentModel::where('sales_id', $data->sales_id)
                                            ->selectRaw("COALESCE(SUM(payment),0) as payment")
                                            ->first();
        $sum_of_payments                    = $dbpaymentsales->payment;

        $dbsales                            = \DB::table('db_sales')->where('id', $data->sales_id)
                                            ->selectRaw("coalesce(sum(grand_total),0) as total")
                                            ->first();
        $payble_total                       = $dbsales->total;
        $payment_status     = '';

        if($payble_total == $sum_of_payments){
			$payment_status ="Paid";
		} else if($sum_of_payments != 0 && ($sum_of_payments < $payble_total)){
			$payment_status = "Partial";
		} else if($sum_of_payments == 0){
			$payment_status ="Unpaid";
		}
        // echo json_encode($dbpaymentsales);
        // update sales
        \DB::table('db_sales')->where('id', $data->sales_id)->update(array(
            'payment_status' => $payment_status,
            'paid_amount'   => $sum_of_payments
        ));

        // update customer
        $dbsalesdue                     = \DB::table('db_sales')->where('customer_id', $data->customer_id)
                                        ->selectRaw("COALESCE(SUM(grand_total),0) - COALESCE(SUM(paid_amount),0) as sales_due")
                                        ->first();
        
        \DB::table('db_customers')->where('id', $data->customer_id)
        ->update(array(
            'sales_due' => $dbsalesdue ? $dbsalesdue->sales_due : ''
        ));
    }
    public function deletePayment(object $data)
    {
        // payment
        $dbsalespayment                     = SalespaymentModel::whereIn('id', $data->payment_id);
        $dbspay                             = $dbsalespayment->get();
        
        
        if(count($dbsalespayment->get()) > 0){
            $dbsalespayment->delete();
        }else{
            return response()->json(['message' => 'error delete', 'code' => 400]);
        }
        foreach($dbspay as $b){
            $sales_id               = $b->sales_id;
            $customer_id            = $b->customer_id;
            $data['sales_id']       = $sales_id;
            $data['customer_id']    = $customer_id;
            $this->updatePaymentsalesstatus($data);
            (new CustomerService())->set_customer_tot_advance($customer_id);
        }
        // update payment sales

        // reset transaction account
       
        $reset_accounts                     = \DB::table('ac_transactions')
                                            ->whereIn('ref_salespayments_id', $data->payment_id)
                                            ->select('debit_account_id', 'credit_account_id')
                                            ->groupBy('debit_account_id', 'credit_account_id')
                                            ->get();
        if(count($reset_accounts) > 0){
            foreach($reset_accounts as $b){
                (new AccounttransactionService())->update_account_balance($b->debit_account_id);
                (new AccounttransactionService())->update_account_balance($b->credit_account_id);
            }
        }

        // update total advance customer
        

        return response()->json(['message' => 'success', 'code' => 200]);
    }
    public function getCode()
    {
        $code = SalespaymentModel::count();
        $code++;
        return str_pad($code, 4, '0', STR_PAD_LEFT);
    }
}
