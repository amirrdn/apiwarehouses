<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\PurchaseService;
use App\Services\PurchasepaymentService;
use App\Services\PurchaseitemService;

class PurchaseController extends Controller
{
    public function totalInvoicePurchase(Request $request, PurchaseService $pu)
    {
        $totalinv                       = \DB::table('db_purchase as b')
                                        ->selectRaw("COUNT(*) as total")
                                        ->where('purchase_status', 'Received')
                                        ->where('b.store_id', \Auth::user()->store_id)
                                        ->count();
        $pur_total                      = \DB::table('db_purchase as b')
                                        ->selectRaw("COALESCE(sum(grand_total),0) AS tot_pur_grand_total")
                                        ->where('purchase_status', 'Received')
                                        ->where('b.store_id', \Auth::user()->store_id)
                                        ->first();
        $tot_paid_amt                   = \DB::table('db_purchase as b')
                                        ->selectRaw("COALESCE(SUM(paid_amount),0) AS paid_amount")
                                        ->where('purchase_status', 'Received')
                                        ->where('b.store_id', \Auth::user()->store_id)
                                        ->first();
        $purchase_due_total             = \DB::table('db_suppliers')
                                        ->selectRaw("COALESCE(SUM(purchase_due),0) AS purchase_due")
                                        ->where('store_id', \Auth::user()->store_id)
                                        ->first();
        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data'  => [
                'total_invoice_purchase'    => $totalinv,
                'total_invoice_amount'  => $pur_total->tot_pur_grand_total,
                'total_paid_amount' => $tot_paid_amt->paid_amount,
                'total_purchase_due'    => $purchase_due_total->purchase_due
            ]
            ]);
    }
    public function listPurchase(Request $request, PurchaseService $pu)
    {
        $purchase                       = $pu->purchaseRelation()
                                        ->select('*')
                                        ->with('supliers');
        if(!empty($request->query_search)){
            $query_search               = $request->query_search;
            $purchase                   = $purchase->whereRaw('upper(purchase_code) LIKE "%'.strtoupper($query_search).'%"');
            // $purchase                   = $purchase->where(function($query) use ($query_search){
            //                                 $query->whereRaw('upper(purchase_code) LIKE "%'.strtoupper($query_search).'%"')
            //                                 ->orWhereRaw('upper(created_by) LIKE "%'.strtoupper($query_search).'%"');
            //                             });
        }
        if(!empty($request->start_date) && !empty($request->end_date)){
            $quotations             = $quotations->whereBetween('purchase_date', [date('Y-m-d', strtotime($request->start_date)), date('Y-m-d', strtotime($request->end_date))]);
        }
        if(!empty($request->order) && !empty($request->sort)){
            $purchase                   = $purchase->orderBy($request->order, $request->sort);
        }
        if(!empty($request->per_page)){
            $purchase                   = $purchase->paginate($request->per_page);
        }else{
            $purchase                   = $purchase->get();
        }

        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data'  => $purchase
        ]);
    }
    public function store(Request $request, PurchaseService $pu)
    {
        $validator = \Validator::make($request->all(), [
            'supplier_id' => 'required',
            'purchase_date' => 'required',
        ],[
            'supplier_id.required' => 'Supplier is required !',
            'purchase_date.required' => 'Purchse Date is required !'
        ]);
        if ($validator->fails()) {
            $error = $validator->errors();
            return response()->json([
                'message'   => $error,
                'code'  => 400
            ]);
        }
        $insert                 = $pu->store($request);

        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data'  => $insert
        ]);
    }
    public function view(Request $request, PurchaseService $pu, PurchasepaymentService $py)
    {
        $pu                         = $pu->purchaseRelation()
                                    ->where('id', $request->purchase_id)
                                    // ->with('supliers')
                                    ->with(['supliers'=>function($query){
                                        $query->with('supplier_store');
                                        // $query->join('db_store as st', 'db_suppliers.store_id', 'st.id')
                                        // ->select('db_suppliers.*', 'st.*');
                                    }])    
                                    ->with('purchase_store')
                                    ->first();
        $purchasepayments           = $py->sqlPayment()
                                    ->where('purchase_id', $request->purchase_id)
                                    ->with('accounts')
                                    ->get();
        $itemdetail                 = (new PurchaseitemService())->sqlPurchaseitem()
                                    ->with('items')
                                    ->with('taxs')
                                    ->where('purchase_id', $request->purchase_id)
                                    ->get();

        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data'  => [
                'purchase'  => $pu,
                'purchase_payment'  => $purchasepayments,
                'purchase_item' => $itemdetail
            ]
        ]);
    }
    public function update(Request $request, PurchaseService $pu)
    {
        $validator = \Validator::make($request->all(), [
            'supplier_id' => 'required',
            'purchase_date' => 'required',
            'purchase_id' => 'required',
        ],[
            'supplier_id.required' => 'Supplier is required !',
            'purchase_date.required' => 'Purchse Date is required !',
            'purchase_id.required' => 'No Data Purchase selected !'
        ]);
        if ($validator->fails()) {
            $error = $validator->errors();
            return response()->json([
                'message'   => $error,
                'code'  => 400
            ]);
        }
        $insert                 = $pu->update($request);

        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data'  => $insert
        ]);
    }
    public function destroy(Request $request, PurchaseService $pu)
    {
        $validator = \Validator::make($request->all(), [
            'purchase_id' => 'required | array',
        ],[
            'supplier_id.required' => 'Supplier is required !',
            'purchase_date.required' => 'Purchse Date is required !',
            'purchase_id.required' => 'No Data Purchase selected !'
        ]);
        if ($validator->fails()) {
            $error = $validator->errors();
            return response()->json([
                'message'   => $error,
                'code'  => 400
            ]);
        }

        return $pu->delete($request->purchase_id);
    }
}
