<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\SalespaymentService;
use App\Services\RolesService;
use App\Services\AccounttransactionService;

class SalespaymentController extends Controller
{
    private RolesService $rls;
    public function __construct(RolesService $rls) {
        $this->access = \Auth::user() ? strtoupper($rls->getRoleByusers(\Auth::user()->role_id)) == strtoupper('store admin') : false;
    }

    public function paymentDetail($sales_id, SalespaymentService $sp)
    {
        if($this->access){
            $salespayment               = $sp->salesPaymentRelations(false, true);
        }else{
            $salespayment               = $sp->salesPaymentRelations(true, false);
        }
        $salespayment                   = $salespayment->where('s.id', request()->sales_id)
                                        ->select('s.*', 's.id as sales_id', 'cs.*', 'cs.id as customer_id', 'cs.tot_advance')
                                        ->groupBy('s.id')
                                        ->first();
        $country                        = \DB::table('db_country')
                                        ->where('id', $salespayment ? $salespayment->country_id : '')
                                        ->first();
        $state                          = \DB::table('db_states')
                                        ->where('id', $salespayment ? $salespayment->state_id : '')
                                        ->first();
        $paymenttype                    = \DB::table('db_paymenttypes')
                                        ->where('status', 1)
                                        ->where('store_id', \Auth::user()->store_id)
                                        ->get();

        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data'  => [
                'sales_payment' => $salespayment,
                'country'   => $country,
                'state' => $state,
                'payment_type'  => $paymenttype,
            ]
            ]);
    }
    public function store(Request $request, SalespaymentService $pay, AccounttransactionService $actransactions)
    {
        $request->merge([
            'payment'   => $request->amount
        ]);
        $checkpayment = \DB::table('db_salespayments')->where('sales_id', $request->sales_id);
        if(count($checkpayment->get()) > 0){
            $checkpayment->delete();
        }
        $request->merge([
            'action'    => 'create'
        ]);
        $insert = $pay->store($request);
        if(!empty($request->account_id)){
            $request->merge([
                'transaction_type'  	=> 'SALES PAYMENT',
                'reference_table_id'  	=> $insert->id,
                'debit_account_id'  	=> null,
                'credit_account_id'  	=> $request->account_id,
                'debit_amt'  			=> 0,
                'credit_amt'  			=> $request->amount,
                'process'  				=> 'SAVE',
                'note'  				=> $request->payment_note,
                'transaction_date'  	=> $insert->created_date,
                'payment_code'  		=> $insert->payment_code,
                'customer_id'  			=> $insert->customer_id,
                'supplier_id'  			=> null,
            ]);
            $actransactions->insert_account_transaction($request);
        }
        
        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data'  => $insert
        ]);
    }
    public function destroy(Request $request, SalespaymentService $pay)
    {
        return $pay->deletePayment($request);
    }
}
