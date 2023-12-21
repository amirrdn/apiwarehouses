<?php

namespace App\Services;
use App\Models\CustomerModel;
use App\Models\SalespaymentModel;
use App\Models\CustomeradvanceModel;
use App\Services\CustomService;
use App\Services\ShippingaddressService;
use App\Services\AccounttransactionService;

use Carbon\Carbon;
use Larinfo;
/**
 * Class CustomerService.
 */
class CustomerService
{
    public function customerRelations($leftjoins = false, $innerjoin = false)
    {
        $customer               = CustomerModel::from('db_customers as cust');
        if($leftjoins){
            $customer           = $customer->leftjoin('db_store as st', 'cust.store_id', 'st.id');
        }else if($innerjoin){
            $customer           = $customer->join('db_store as st', 'cust.store_id', 'st.id');
        }

        return $customer;
    }
    public function store(object $data)
    {
        $data['action']         = 'create';
        return $this->insertOrUpdate($data);
    }
    public function update(object $data)
    {
        $data['action']         = 'update';
        return $this->insertOrUpdate($data);
    }
    public function insertOrUpdate(object $data)
    {
        $larinfo                            = Larinfo::getInfo();
        if($data->action == 'create'){
            $custcode                       = $this->getcode();
            $codecust                       = (new StoreService())->getStoreByid(\Auth::user()->store_id)->customer_init.$custcode;
            $customers                      = new CustomerModel;
            $customers->store_id            = \Auth::user()->store_id;
            $customers->count_id            = CustomerModel::count() + 1;
            $customers->created_time        = Carbon::now()->format('h:i:s');
            $customers->created_by          = \Auth::user()->username;
            $customers->system_ip           = request()->ip();
            $customers->system_name         = $larinfo['server']['software']['os'];
            $customers->created_date        = Carbon::now()->format('Y-m-d');
            $customers->customer_code       = $codecust;
        }else{
            $customers                      = CustomerModel::find($data->customer_id);
        }

        $customers->customer_name           = $data->customer_name;
        $customers->mobile                  = $data->mobile;
        $customers->phone                   = $data->phone;
        $customers->email                   = $data->email;
        $customers->gstin                   = $data->gstin;
        $customers->tax_number              = $data->tax_number;
        $customers->vatin                   = $data->vatin;
        $customers->opening_balance         = $data->opening_balance;
        $customers->sales_due               = $data->sales_due;
        $customers->sales_return_due        = $data->sales_return_due;
        $customers->country_id              = $data->country_id;
        $customers->state_id                = $data->state_id;
        $customers->city                    = $data->city;
        $customers->postcode                = $data->postcode;
        $customers->address                 = $data->address;
        $customers->ship_country_id         = $data->ship_country_id;
        $customers->ship_state_id           = $data->ship_state_id;
        $customers->ship_city               = $data->ship_city;
        $customers->ship_postcode           = $data->ship_postcode;
        $customers->ship_address            = $data->ship_address;
        $customers->company_id              = $data->company_id;
        $customers->status                  = $data->status ? $data->status : 1;
        $customers->location_link           = $data->location_link;
        $customers->attachment_1            = $data->attachment_1;
        $customers->price_level_type        = $data->price_level_type;
        $customers->price_level             = $data->price_level;
        $customers->delete_bit              = $data->delete_bit ? $data->delete_bit : '';
        $customers->tot_advance             = $data->tot_advance;
        $customers->credit_limit            = $data->credit_limit;
        $customers->shippingaddress_id      = $data->shippingaddress_id;

        $customers->save();
        if(!empty($data->ship_country_id)){
            $data['customer_id']            = $customers->id;
            $data['store_id']               = $customers->store_id;
            (new ShippingaddressService())->store($data);
        }
        return $customers;
    }
    public function delete(array $customer_id)
    {
        $dbsales                            = \DB::table('db_sales')->whereIn('customer_id', $customer_id);
        $datasales                          = $dbsales->get();
        if(count($datasales) > 0){
            return response()->json([
                'message' => 'Sales Invoices Exist of Customer! Please Delete Sales Invoices!', 
                'code' => 400
            ]);
        }
        $dbsalespayment                     = \DB::table('db_salespayments')->whereIn('customer_id', $customer_id);
        $dbsalespayment                     = $dbsalespayment->where('short_code', 'OPENING BALANCE PAID');
        $datasalespayment                   = $dbsalespayment->get();
        if(count($datasalespayment) > 0){
            $datasalespayment->delete();
        }
        $dbcustomer                         = CustomerModel::whereIn('id', $customer_id);
        $datacustomer                       = $dbcustomer->get();

        if(count($datacustomer) > 0){
            foreach($datacustomer as $b){
                $dbship = \DB::table('db_shippingaddress')->whereIn('customer_id', $customer_id);
                if(count($dbship->get()) > 0){
                    $dbship->delete();
                }
                if(\File::exists(public_path('/'.$b->attachment_1))){
                    \File::delete(public_path('/'.$b->attachment_1));
                }
            }
            $dbcustomer->delete();
        }
        $reset_accounts             = \DB::table('ac_transactions')
                                    ->whereIn('customer_id', $customer_id)
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
    public function get_customer_details($customer_id)
    {
        $dbcust         = $this->customerRelations()
                        ->where('id', $customer_id)
                        ->select('*')
                        ->groupBy('id')
                        ->get();
        return $dbcust;
    }
    public function set_customer_tot_advance($customer_id)
    {
        $tot_advance            = $this->get_customer_tot_advance($customer_id);

        CustomerModel::where('id', $customer_id)
        ->update([
            'tot_advance' => $tot_advance
        ]);
    }
    public function get_customer_tot_advance($customer_id)
    {
        $adv                    = CustomeradvanceModel::where('customer_id', $customer_id)
                                ->selectRaw("coalesce(sum(amount),0) as tot_advance")
                                ->first();
        $tot_advance            = $adv ? $adv->tot_advance : 0;

        $salespay               = SalespaymentModel::where('customer_id', $customer_id)
                                ->selectRaw("coalesce(sum(advance_adjusted),0) as advance_adjusted")
                                ->first();
        
        $advance_adjusted       = $salespay ? $salespay->advance_adjusted : 0;
        $tot_advance -= $advance_adjusted;
        return $tot_advance;
    }
    public function check_credit_limit_with_invoice($customer_id, $sales_id)
    {
        $limit_of   = 0;
        $credit_limit = $this->get_customer_details($customer_id)->first();
        if($credit_limit){
            $limit_of = $credit_limit->credit_limit;
        }else{
            $credit_limit = 0;
        }
        return $credit_limit;
        $balance = $this->get_customer_details($customer_id)->first();
        if(!empty($balance)){
            $balance->sales_due;
            if(!empty($credit_limit) && $credit_limit!=-1 && $balance>$credit_limit){
                return 'This Customer Credit Limit exceeds! Credit Limit :'.(new CustomService())->store_number_format($credit_limit ? $credit_limit : 0)."\nCrossing Credit Amount(Previous+Current Invoice) :".(new Custom())->store_number_format($balance);
            }
        }
    }
    public function getcode()
    {
        $code = CustomerModel::count();
        $code++;
        return str_pad($code, 4, '0', STR_PAD_LEFT);
    }
}
