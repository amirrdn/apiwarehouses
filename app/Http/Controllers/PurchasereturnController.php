<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\PurchasereturnService;
use App\Services\PurchasepaymentreturnService;
use App\Services\PurchasereturnitemService;

class PurchasereturnController extends Controller
{
    public function returnList(Request $request, PurchasereturnService $pureturn)
    {
        $pureturns                  = $pureturn->purchaseSql();
        if(!empty($request->query_search)){
            $query_search           = $request->query_search;

            $pureturns              = $pureturns->where(function($query) use ($query_search){
                                        $query->whereRaw('upper(return_code) LIKE "%'.strtoupper($query_search).'%"')
                                        ->orWhereRaw('upper(payment_status) LIKE "%'.strtoupper($query_search).'%"');
                                    });
        }
        if(!empty($request->start_end) && !empty($request->end_date)){
            $pureturns              = $pureturns->whereBetween('return_date', [date('Y-m-d', strtotime($request->start_date)), date('Y-m-d', strtotime($request->end_date))]);
        }
        $pureturns                  = $pureturns
                                    ->with('purchases')
                                    ->with('suppliers');
        if(!empty($request->order) && !empty($request->sort)){
            $pureturns              = $pureturns->orderBy($request->order, $request->sort);
        }else{
            $pureturns              = $pureturns->orderBy('return_date', 'desc');
        }
        $pureturns                  = $pureturns->select('pur.*', 'payret.payment as amount');
        if(!empty($request->per_page)){
            $pureturns              = $pureturns->paginate($request->per_page);
        }else{
            $pureturns              = $pureturns->get();
        }

        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data'  => $pureturns
        ]);
    }
    public function store(Request $request, PurchasereturnService $pureturn)
    {
        if($request->warehouse_id){
            return $pureturn->store($request);
        }else{
            return response()->json([
                'message'   => 'please check format json parameter',
                'code'  => 400
            ]);
        }
    }
    public function view(Request $request, PurchasereturnService $pureturn, PurchasepaymentreturnService $payment, PurchasereturnitemService $itemreturn)
    {
        $poreturn                       = $pureturn->purchaseSql()
                                        ->where('pur.id', $request->purchase_return_id)
                                        ->with('purchases')
                                        ->with('suppliers')
                                        ->with('stores')
                                        ->select('pur.*')
                                        ->first();
        $paymentreturn                  = $payment->paymentreturnSql()
                                        ->where('return_id', $request->purchase_return_id)
                                        ->with('accounts')
                                        ->first();
        $purchase_item_return           = $itemreturn->poReturnsql()
                                        ->where('return_id', $request->purchase_return_id)
                                        ->with('items')
                                        ->with('taxs')
                                        ->first();

        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data'  => $poreturn,
            'payment_purchase_return'   => $paymentreturn,
            'purchase_item_return'  => $purchase_item_return
        ]);
    }
    public function update(Request $request, PurchasereturnService $pureturn)
    {
        if($request->warehouse_id){
            return $pureturn->update($request);
        }else{
            return response()->json([
                'message'   => 'please check format json parameter',
                'code'  => 400
            ]);
        }
    }
    public function viewPaymentReturn(Request $request, PurchasepaymentreturnService $payment)
    {
        $paymentreturn                  = $payment->paymentreturnSql()
                                        ->where('return_id', $request->purchase_return_id)
                                        ->with('accounts')
                                        ->with('suppliers')
                                        ->with('purchases')
                                        ->first();

        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data'  => $paymentreturn
        ]);
    }
}
