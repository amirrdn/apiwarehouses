<?php

namespace App\Services;

use App\Models\PurchasepaymentreturnModel;

use App\Services\StoreService;
/**
 * Class PurchaseReturnPaymentService.
 */

 use Larinfo;
use Carbon\Carbon;
class PurchaseReturnPaymentService
{
    public function store(object $data)
    {
        $larinfo                    = Larinfo::getInfo();

        $getstore                   = (new StoreService())->getStoreByid(\Auth::user()->store_id)->purchase_return_init;
        $payment                    = new PurchasepaymentreturnModel;

        $payment->payment_code      = $getstore.$this->code();
        $payment->store_id          = $data->store_id;
        $payment->purchase_id       = $data->purchase_id;
        $payment->return_id         = $data->return_id;
        $payment->payment_date      = Carbon::now()->format('Y-m-d');
        $payment->payment_type      = $data->payment_type;
        $payment->payment           = $data->payment;
        $payment->payment_note      = $data->payment_note;
        $payment->system_ip         = request()->ip();
        $payment->system_name       = $larinfo['server']['software']['os'];
        $payment->count_id          = PurchasepaymentreturnModel::count() + 1;
        $payment->store_id          = \Auth::user()->store_id;
        $payment->created_date      = Carbon::now()->format('Y-m-d');
        $payment->created_time      = Carbon::now()->format('h:i:s');
        $payment->created_by        = \Auth::user()->username;
        $payment->status            = 1;
        $payment->account_id        = $data->account_id;
        $payment->supplier_id       = $data->supplier_id;

        $payment->save();

        return $payment;
    }
    public function code(){
        $code = PurchasepaymentreturnModel::count() + 1;
        $code++;
        return str_pad($code, 4, '0', STR_PAD_LEFT);
    }
}
