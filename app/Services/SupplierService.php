<?php

namespace App\Services;

use App\Models\SupplierModel;

use App\Services\StoreService;
use App\Services\PurchasepaymentService;

use Larinfo;
use Carbon\Carbon;
/**
 * Class SupplierService.
 */
class SupplierService
{
    public function supplierRelations()
    {
        $suplier                        = SupplierModel::from('db_suppliers as sp')
                                        ->leftjoin('db_store as st', 'sp.store_id', 'st.id')
                                        ->leftjoin('db_country as c', 'sp.country_id', 'c.id')
                                        ->leftjoin('db_states as stt', 'sp.state_id', 'stt.id')
                                        ->leftjoin('db_purchase as pu', 'sp.id', 'pu.supplier_id');
        return $suplier;
    }
    public function store(object $data)
    {
        $data['action']                 = 'create';
        return $this->insertOrupdate($data);
    }
    public function update(object $data)
    {
        $data['action']                 = 'update';
        return $this->insertOrupdate($data);
    }
    public function insertOrupdate(object $data)
    {
        $larinfo = Larinfo::getInfo();
        if($data->action === 'create'){
            $codesupplier               = $this->getCode();
            $suppliercode               = (new StoreService())->getStoreByid(\Auth::user()->store_id)->supplier_init.$codesupplier;
            $supplier                   = new SupplierModel;
            $supplier->store_id         = \Auth::user()->store_id;
            $supplier->count_id         = SupplierModel::count() + 1;
            $supplier->supplier_code    = $suppliercode;
            $supplier->system_ip        = request()->ip();
            $supplier->system_name      = $larinfo['server']['software']['os'];
            $supplier->created_date     = Carbon::now()->format('Y-m-d');
            $supplier->created_time     = Carbon::now()->format('h:i:s');
            $supplier->created_by       = \Auth::user()->id;
        }else if($data->action === 'update'){
            $supplier                   = SupplierModel::find($data->supplier_id);
            $codesupplier               = $this->getCode();
            $suppliercode               = (new StoreService())->getStoreByid(\Auth::user()->store_id)->supplier_init.$codesupplier;
            $supplier->supplier_code    = $suppliercode;
        }else{
            $supplier                   = new SupplierModel;            
        }

        $supplier->supplier_name        = $data->supplier_name;
        $supplier->mobile               = $data->mobile;
        $supplier->phone                = $data->phone;
        $supplier->email                = $data->email;
        $supplier->gstin                = $data->gstin;
        $supplier->tax_number           = $data->tax_number;
        $supplier->vatin                = $data->vatin;
        $supplier->opening_balance      = $data->opening_balance ? $data->opening_balance : NULL;
        $supplier->purchase_due         = $data->purchase_due ? $data->purchase_due : NULL;
        $supplier->purchase_return_due  = $data->purchase_return_due ? $data->purchase_return_due : NULL;
        $supplier->country_id           = $data->country_id;
        $supplier->state_id             = $data->state_id ? $data->state_id : NULL;
        $supplier->city                 = $data->city ? $data->city : NULL;
        $supplier->postcode             = $data->postcode ? $data->postcode : NULL;
        $supplier->address              = $data->address ? $data->address : NULL;
        $supplier->company_id           = $data->company_id ? $data->company_id : NULL;
        $supplier->status               = 1;

        $supplier->save();
        return $supplier;
    }
    public function delete(array $supplier_id)
    {
        $purcahse                       = $this->supplierRelations()
                                        ->whereIn('supplier_id', $supplier_id)
                                        ->select('*')
                                        ->get();
        $message = array();
        if(count($purcahse) > 0){
            $message = ['message' => 'Purchase Invoices Exist of Supplier! Please Delete Purchase Invoices!', 'code' => 400];
        }
        $reset_accounts                 = \DB::table('ac_transactions')
                                        ->whereIn('supplier_id', $supplier_id)
                                        ->groupBy('debit_account_id', 'credit_account_id')
                                        ->get();
        $dbsupplier                     = SupplierModel::whereIn('id', $supplier_id);
        $dbpurchasepayments             = \DB::table('db_purchasepayments')->whereIn('supplier_id', $supplier_id)
                                        ->where('short_code', 'OPENING BALANCE PAID');
        if(count($dbpurchasepayments->get()) > 0){
            $deletepupayment            = $dbpurchasepayments->delete();
        }
        if(count($dbsupplier->get()) > 0){
            $deletesupplier             = $dbsupplier->delete();
        }else{
            return response()->json(['message' => 'no data delete', 'code' => 400]);
        }
        if(count($reset_accounts) > 0){
            foreach($reset_accounts as $b){
                (new PurchasepaymentService())->update_purchase_payment_status_by_purchase_id($b->debit_account_id);
                (new PurchasepaymentService())->update_purchase_payment_status_by_purchase_id($b->credit_account_id);
            }
        }
        return response()->json(['message' => 'success delete', 'code' => 200]);
    }
    public function getCode()
    {
        $code = SupplierModel::count() - 1;
        $code++;
        return str_pad($code, 4, '0', STR_PAD_LEFT);
    }
}
