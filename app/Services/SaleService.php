<?php

namespace App\Services;
use App\Models\SalesModel;
use App\Models\SalespaymentModel;
use App\Services\StoreService;
use App\Services\ItemsService;
use App\Services\WarehouseService;
use App\Services\SalesitemService;
use App\Services\CustomerService;
use App\Services\SalespaymentService;
use App\Services\SmsService;
use App\Services\CustomService;
use App\Services\AccounttransactionService;
use Carbon\Carbon;
use Larinfo;
/**
 * Class SaleService.
 */
class SaleService
{
    private StoreService $st;
    private ItemsService $itms;
    public function __construct(StoreService $st, ItemsService $itms) {
        $this->itms                         = $itms;
        $this->st                           = $st;
        $larinfo                            = Larinfo::getInfo();
        $this->date_now                     = Carbon::now()->format('Y-m-d');
        $this->ip                           = request()->ip();
        $this->info                         = $larinfo['server']['software']['os'];
        $this->timenow                      = Carbon::now()->format('h:i:s');
    }
    public function SalesRelations()
    {
        return SalesModel::from('db_sales as s')
        ->leftjoin('db_store as st', 's.store_id', 'st.id');
    }
    public function store(object $data){
        $data['action'] = 'create';
        return $this->insertorupdateData($data);
    }
    public function update(object $data)
    {
        $data['action'] = 'update';
        return $this->insertorupdateData($data);
    }
    public function insertorupdateData(object $data)
    {
        if($data->action == 'create'){
            $vsales                             = new SalesModel;
            $salescode                          = $this->getCode();
            $initsales                          = $this->st->getStoreByid(\Auth::user()->store_id)->sales_init;
            
            $vsales->store_id                   = \Auth::user()->store_id;
            $vsales->init_code                  = $initsales;
            $vsales->count_id                   = SalesModel::max('count_id') + 1;
            $vsales->sales_code                 = $initsales.$salescode;
            $vsales->created_date               = $this->date_now;
            $vsales->created_time               = $this->timenow;
            $vsales->created_by                 = \Auth::user()->username;
            $vsales->system_ip                  = $this->ip;
            $vsales->system_name                = $this->info;
            $vsales->sales_date                 = $data->sales_date ? date('Y-m-d', strtotime($data->sales_date)) : date('Y-m-d', strtotime($this->date_now));
            $vsales->sales_status               = $data->sales_status;
        }else{
            $vsales                             = SalesModel::where('id', $data->sales_id)->first();
        }
        if(!empty($data->due_date)){
            $vsales->due_date               = date('Y-m-d', strtotime($data->due_date));
        }
        $vsales->reference_no               = $data->reference_no != null ? $data->reference_no : "";
        $vsales->customer_id                = $data->customer_id;
        $vsales->other_charges_input        = $data->other_charges_input;
        $vsales->other_charges_tax_id       = $data->other_charges_tax_id;
        $vsales->other_charges_amt          = $data->other_charges_amt;
        $vsales->discount_to_all_input      = $data->discount_to_all_input;
        $vsales->discount_to_all_type       = $data->discount_to_all_type;
        $vsales->tot_discount_to_all_amt    = $data->tot_discount_to_all_amt;
        $vsales->subtotal                   = $data->subtotal;
        $vsales->round_off                  = $data->round_off;
        $vsales->grand_total                = $data->grand_total;
        $vsales->sales_note                 = $data->sales_note;
        $vsales->payment_status             = $data->payment_status;
        $vsales->paid_amount                = $data->paid_amount;
        $vsales->company_id                 = $data->company_id;
        $vsales->pos                        = $data->pos;
        $vsales->status                     = 1;
        $vsales->return_bit                 = $data->return_bit;
        $vsales->customer_previous_due      = $data->customer_previous_due;
        $vsales->customer_total_due         = $data->customer_total_due;
        $vsales->quotation_id               = $data->quotation_id;
        $vsales->coupon_id                  = $data->coupon_id;
        $vsales->coupon_amt                 = $data->coupon_amt;
        $vsales->invoice_terms              = $data->invoice_terms;
        $vsales->warehouse_id               = $data->warehouse_id;

        $vsales->save();

        if(isset($data->quotation_id) && !empty($data->quotation_id)){
            \DB::table('db_quotation')->where('id', $data->quotation_id)
            ->update(array(
                'sales_status' => 'Converted'
            ));
        }
        $datac = [];
        $dataitem  = [];
        // $checkitemonsales = \DB::table('db_salesitems')->where('sales_id', $vsales->id)->get();

        if(count($data->items) > 0){
            \DB::table('db_salesitems')->where('sales_id', $vsales->id)->delete();
            foreach($data->items as $b){
                $discount_amt_per_unit = $b['discount_amt'] / $b['sales_qty'];

                if($b['tax_type'] == 'Exclusive'){
					$single_unit_total_cost = $b['price_per_unit'] + ($b['unit_tax'] * $b['price_per_unit'] / 100);
				} else {//Inclusive
					$single_unit_total_cost = $b['price_per_unit'];
				}
                $single_unit_total_cost -= $discount_amt_per_unit;

                $item_details = $this->itms->get_item_details($b['item_id'], $vsales->warehouse_id);
                $item_name = $item_details ? $item_details['item_name'] : '';
				$service_bit = $item_details ? $item_details['service_bit'] : '';
				$purchase_price = $item_details ? $item_details['price'] : 0;
                $current_stock_of_item = (new WarehouseService())->total_available_qty_items_of_warehouse($data->warehouse_id,$vsales->store_id,$b['item_id']);

                if($current_stock_of_item < $b['sales_qty'] && $service_bit == 0){
                    // $deletedbsales = \DB::table('db_sales')
                    //                 ->where('id', $vsales->id);
                    // if(count($deletedbsales->get()) > 0){
                    //     $deletedbsales->delete();
                    // }
					return response()->json([
                        'message' => $item_name." has only ".$current_stock_of_item." in Stock!!",
                        'code'  => 400
                    ]);
				}
                $datasalesitem          = [
                    'sales_id' 			=> $vsales->id, 
		    		'sales_status'		=> $data->sales_status, 
		    		'item_id' 			=> $b['item_id'], 
		    		'description' 		=> $b['description'], 
		    		'sales_qty' 		=> $b['sales_qty'],
		    		'price_per_unit' 	=> $b['price_per_unit'],
		    		'tax_type' 			=> $b['tax_type'],
		    		'tax_id' 			=> $b['tax_id'],
		    		'tax_amt' 			=> $b['tax_amt'],
		    		'discount_input' 	=> $b['discount_input'],
		    		'discount_amt' 		=> $b['discount_amt'],
		    		'discount_type' 	=> $b['discount_type'],
		    		'unit_total_cost' 	=> $single_unit_total_cost,
		    		'total_cost' 		=> $b['total_cost'],
		    		'purchase_price' 	=> $purchase_price,
		    		'status'	 		=> 1,
		    		'seller_points'		=> $item_details['seller_points'] * $b['sales_qty'],
                    'sales_item_id'     => isset($b['sales_item_id']) ? $b['sales_item_id'] : null,
                    'action'            => 'create',
                    'store_id'          => $vsales->store_id,
                ];
              (new SalesitemService())->store((object) $datasalesitem);
                (new ItemsService())->update_items_quantity($b['item_id'], $vsales->warehouse_id, $vsales->store_id);
            }
        }
        if($data->amount > 0 && !empty($data->payment_type)){
            if($data->amount > $data->grand_total){
                return response()->json([
                    'message'   => 'Payble amount should not be exceeds Invoice Amount!!',
                    'code'  => 404
                ]);
            }
            $dbpayments         = \DB::table('db_salespayments')
                                ->where('sales_id', $vsales->id)
                                ->selectRaw("coalesce(sum(payment),0) as payment");

            $advance_adjusted = 0;

            if(!empty($data->allow_tot_advance) && $data->allow_tot_advance == 'on'){
				$dbcusts = (new CustomerService())->get_customer_details($data->customer_id);
                if(count($dbcusts) > 0){
                    $tot_advance = $dbcusts->first()->tot_advance;
                    if($tot_advance > 0){
                        if($data->amount == $tot_advance){
                            $advance_adjusted = $data->amount;
                        } else if($data->amount > $tot_advance){
                            $advance_adjusted = $tot_advance;	
                        } else{
                            $advance_adjusted = $data->amount;
                        }
                    }
                }
			}
            $action_new = $data->action;
            if($data->action == 'update'){
                if(count($dbpayments->get()) > 0 && empty($data->sales_payment_id)){
                    $dbpayments->delete();
                    $action_new = 'create';
                }
            }
            // if($dbpayments->payment == 0 && $data->action != 'create'){
                $datapayment = array(
                    'payment_date'		=> $vsales->sales_date,//Current Payment with sales entry
                    'payment_type' 		=> $data->payment_type,
                    'payment' 			=> $data->amount,
                    'payment_note' 		=> $data->payment_note,
                    'account_id' 		=> $data->account_id,
                    'customer_id' 		=> $data->customer_id,
                    'advance_adjusted' 	=> $advance_adjusted,
                    'cheque_number' 	=> $data->cheque_number,
                    'cheque_period' 	=> $data->cheque_period,
                    'cheque_status' 	=> "Pending",
                    'action'            => $action_new,
                    'store_id'          => $vsales->store_id,
                    'sales_id'          => $vsales->id,
                    'sales_payment_id'  => $data->sales_payment_id
                );
                $dataitem []= $datapayment;
                $salespayment_id = (new SalespaymentService())->store((object) $datapayment);
                if(!empty($data->account_id)){
                    $data['transaction_type']   = 'SALES PAYMENT';
                    $data['reference_table_id'] = $salespayment_id->id;
                    $data['debit_account_id']   = null;
                    $data['credit_account_id']  = $data->account_id;
                    $data['debit_amt']          = 0;
                    $data['credit_amt']         = $data->amount;
                    $data['process']            = 'SAVE';
                    $data['note']               = $data->payment_note;
                    $data['transaction_date']   = $salespayment_id->created_date;
                    $data['payment_code']       = $salespayment_id->payment_code;
                    $data['customer_id']        = $data->customer_id;
                    $data['supplier_id']        = null;
                }
                (new AccounttransactionService())->insert_account_transaction($data);
            // }
        }
        $this->update_sales_payment_status_by_sales_id($vsales->id, $data->customer_id);
        (new CustomerService())->set_customer_tot_advance($data->customer_id);
        (new CustomerService())->check_credit_limit_with_invoice($vsales->customer_id, $vsales->id);

        if(isset($data->send_sms) && $data->send_sms == 'on'){
            (new SmsService())->send_sms_using_template($sales_id, 1);
        }
        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data'  => [
                'sales_id' => $vsales->id,
                'items' => $dataitem
            ]
        ]);
    }
    public function update_sales_payment_status_by_sales_id($sales_id, $customer_id)
    {
        $salepay                            = SalespaymentModel::where('sales_id', $sales_id)
                                            ->selectRaw("COALESCE(SUM(payment),0) as payment")
                                            ->first();
        $sum_of_payments                    = $salepay->payment;
        $payble                             = SalesModel::where('id', $sales_id)
                                            ->selectRaw("coalesce(sum(grand_total),0) as total")
                                            ->first();
        $payble_total                       = $payble->total;

        $payment_status = '';

        if($payble_total == $sum_of_payments){
			$payment_status = "Paid";
		}else if($sum_of_payments !== 0 && $sum_of_payments < $payble_total){
			$payment_status = "Partial";
		}else if($sum_of_payments === 0){
			$payment_status = "Unpaid";
		}
        SalesModel::where('id', $sales_id)
        ->update([
            'payment_status' => $payment_status,
            'paid_amount'   => $sum_of_payments
        ]);
        $sales                              = SalesModel::where('customer_id', $customer_id)
                                            ->where('sales_status', 'Final')
                                            ->selectRaw("COALESCE(SUM(grand_total),0) - COALESCE(SUM(paid_amount),0) as sales_due")
                                            ->first();
        \DB::table('db_customers')
        ->where('id', $customer_id)
        ->update([
            'sales_due' => $sales->sales_due
        ]);
        (new CustomerService())->set_customer_tot_advance($customer_id);
    }
    public function deleteSales(object $data)
    {
        $dbsalereturn                   = \DB::table('db_salesreturn')->whereIn('sales_id', $data->sales_id)->get();

        if(count($dbsalereturn) > 0){
            $dbsales                    = SalesModel::whereIn('id', $data->sales_id)->get();
            $codesales  = array();

            foreach($dbsales as $c){
                $codesales[] = $c->sales_code;
            }
            $ccode = implode(', ', $codesales);
            return response()->json([
                'message' => 'Invoice <br />'.$ccode.'Already Raised Returns, Please Delete Before Deleting Original Invoice',
                'code'  => 400
            ]);
        }

        $customer_records               = SalesModel::whereIn('id', $data->sales_id)
                                        ->where('store_id', \Auth::user()->store_id)
                                        ->selectRaw("customer_id,id as sales_id")
                                        ->groupBy('customer_id')
                                        ->get();

        $salesdb                        = SalesModel::whereIn('id', $data->sales_id)
                                        ->where('store_id', \Auth::user()->store_id);
        
        $prev_item_ids                  = \DB::table('db_salesitems as si')
                                        ->join('db_sales as s', 'si.sales_id', 's.id')
                                        ->whereIn('si.sales_id', $data->sales_id)
                                        ->select('si.item_id', 's.warehouse_id', 's.store_id');

        $updateitem = [];
        if(count($prev_item_ids->get()) > 0){
            foreach($prev_item_ids->get() as $b){
                $updateitem             []= (new ItemsService())->update_items_quantity($b->item_id, $b->warehouse_id, $b->store_id);
            }
            $prev_item_ids->delete();
        }

        if(count($customer_records) > 0){
            foreach($customer_records as $b){
                $this->update_sales_payment_status_by_sales_id($b->sales_id, $b->customer_id);
            }
        }
        if(count($salesdb->get()) > 0){
            $salesdb->delete();
        }else{
            return response()->json([
                'message'   => 'error delete',
                'code'  => 400,
                'data' => $updateitem
            ]);
        }
        $reset_accounts             = \DB::table('ac_transactions')
                                    ->whereIn('ref_salespayments_id', $data->sales_id)
                                    ->select('debit_account_id', 'credit_account_id')
                                    ->groupBy('debit_account_id', 'credit_account_id')
                                    ->get();
        
        if(count($reset_accounts) > 0){
            foreach($reset_accounts as $b){
                (new AccounttransactionService())->update_account_balance($b->debit_account_id);
                (new AccounttransactionService())->update_account_balance($b->credit_account_id);
            }
        }
        if(count($customer_records) > 0){
            foreach($customer_records as $b){
                (new CustomerService())->set_customer_tot_advance($b->customer_id);
            }
        }

        return response()->json([
            'message'   => 'success delete',
            'code'      => 200,
            'data' => $updateitem
        ]);
    }
    public function getCode()
    {
        $code = SalesModel::count();
        $code++;
        return str_pad($code, 1, '0', STR_PAD_LEFT);
    }
}
