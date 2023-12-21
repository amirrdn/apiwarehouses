<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\QuotationService;
use App\Services\QuotationitemService;

Use Exception;

class QuotationController extends Controller
{
    public function quotationList(Request $request, QuotationService $qt)
    {
        try{
            $quotations                 = $qt->quotationSql()
                                        ->select('qt.*')
                                        ->with('customers')
                                        ->with('storequotation')
                                        ->with('quotationwarehouse');
            if(!empty($request->query_search)){
                $query_search           = $request->query_search;

                $quotations             = $quotations->where(function($query) use ($query_search){
                                            $query->whereRaw('upper(quotation_code) LIKE "%'.strtoupper($query_search).'%"')
                                            ->orWhereRaw('upper(sales_status) LIKE "%'.strtoupper($query_search).'%"');
                                        });
            }
            if(!empty($request->start_date) && !empty($request->end_date)){
                $quotations             = $quotations->whereBetween('qt.quotation_date', [date('Y-m-d', strtotime($request->start_date)), date('Y-m-d', strtotime($request->end_date. " +1 days"))]);
            }
            $quotations                 = $quotations->groupBy('qt.id')
                                        ->select('qt.*', 's.id as sales_id');
            if(!empty($request->order) && !empty($request->sort)){
                $quotations             = $quotations->orderBy($request->order, $request->sort);
            }else{
                $quotations             = $quotations->orderBy('qt.quotation_code', 'asc');
            }
            if(!empty($request->per_page)){
                $quotations             = $quotations->paginate($request->per_page);
            }else{
                $quotations             = $quotations->get();
            }
            return response()->json([
                'message'   => 'success',
                'code'  => 200,
                'data'  => $quotations
            ]);
        } catch(Exception $e) {
            return response()->json([
                'message'   => $e->getMessage()
            ], 400);
        }
    }
    public function store(Request $request, QuotationService $qt)
    {
        try{
            $validator = \Validator::make($request->all(), [
                'quotation_date' => 'required',
                'customer_id' => 'required'
            ],[
                'quotation_date.required' => 'Quotation Date is required !',
                'customer_id.required' => 'Customer Name is required !'
            ]);
            if ($validator->fails()) {
                $error = $validator->errors();
                return response()->json([
                    'message'   => $error,
                    'code'  => 400
                ]);
            }
            return $qt->store($request);
        } catch(Exception $e) {
            return response()->json([
                'message'   => $e->getMessage()
            ], 400);
        }
    }
    public function view($quotation_id, QuotationService $qt, QuotationitemService $itm)
    {
        try{
            $quotations                     = $qt->quotationSql()
                                            ->select('qt.*')
                                            ->with(['customers' => function ($query) {
                                                $query->leftjoin('db_country as cy', 'db_customers.country_id', 'cy.id')
                                                ->leftjoin('db_states as stt', 'db_customers.state_id', 'stt.id')
                                                ->select('db_customers.*', 'cy.id as countryid', 'cy.country', 'stt.state');
                                            }])
                                            ->with('storequotation')
                                            ->with('quotationwarehouse')
                                            ->where('qt.id', $quotation_id)
                                            ->first();
            $items                          = $itm->quotationRelation()
                                            ->with('items')
                                            ->where('qitm.quotation_id', $quotation_id)
                                            ->select('qitm.*','itm.item_name', 'itm.description')
                                            ->get();
            return response()->json([
                'message'   => 'success',
                'code'  => 200,
                'data'  => [
                    'quotation' => $quotations,
                    'items' => $items
                ]
            ]);
        } catch(Exception $e) {
            return response()->json([
                'message'   => $e->getMessage()
            ], 400);
        }
    }
    public function update(Request $request, QuotationService $qt)
    {
        try{
            $validator = \Validator::make($request->all(), [
                'quotation_id' => 'required',
                'quotation_date' => 'required',
                'customer_id' => 'required'
            ],[
                'quotation_id.required' => 'Quotation is required !',
                'quotation_date.required' => 'Quotation Date is required !',
                'customer_id.required' => 'Customer Name is required !'
            ]);
            if ($validator->fails()) {
                $error = $validator->errors();
                return response()->json([
                    'message'   => $error,
                    'code'  => 400
                ]);
            }
            return $qt->update($request);
        } catch(Exception $e) {
            return response()->json([
                'message'   => $e->getMessage()
            ], 400);
        }
    }
    public function destroy(Request $request, QuotationService $qt)
    {
        try{
            $validator = \Validator::make($request->all(), [
                'quotation_id' => 'required|array',
            ],[
                'quotation_id.required' => 'No data selected !',
            ]);
            if ($validator->fails()) {
                $error = $validator->errors();
                return response()->json([
                    'message'   => $error,
                    'code'  => 400
                ]);
            }
            return $qt->delete($request->quotation_id);
        } catch(Exception $e) {
            return response()->json([
                'message'   => $e->getMessage()
            ], 400);
        }
    }
}
