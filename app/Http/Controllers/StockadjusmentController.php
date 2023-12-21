<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\StockadjustmentService;
use App\Services\StockadjustmentitemService;
use App\Services\RolesService;

Use Exception;

class StockadjusmentController extends Controller
{
    public function __construct(RolesService $rls) {
        $this->access = \Auth::user() ? strtoupper($rls->getRoleByusers(\Auth::user()->role_id)) == strtoupper('store admin') : false;
    }

    public function stockadjustList(Request $request, StockadjustmentService $adjustment)
    {
        if($this->access){
            $adjust                     = $adjustment->stockadjustRelations(false, true);
        }else{
            $adjust                     = $adjustment->stockadjustRelations(true, false);
        }
        if(!empty($request->query_search)){
            $adjust                     = $adjust->where('sa.reference_no', 'LIKE', '%'.$request->query_search.'%');
        }
        if(!empty($request->adjustment_date)){
            $adjust                     = $adjust->whereDate('adjustment_date', date('Y-m-d', strtotime($request->adjustment_date)));
        }
        $adjust                         = $adjust->select('sa.*', 'sa.id as adjustment_id', 'st.*', 'sa.created_by as users');
        if(!empty($request->order) && !empty($request->sort)){
            $adjust                     = $adjust->orderBy($request->order, $request->sort);
        }else{
            $adjust                     = $adjust->orderBy('sa.created_date', 'asc');
        }
        if(!empty($request->per_page)){
            $adjust                     = $adjust->paginate($request->per_page);
        }else{
            $adjust                     = $adjust->get();
        }

        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data'  => $adjust
        ]);
    }
    public function store(Request $request, StockadjustmentService $adjustment)
    {
        $validator = \Validator::make($request->all(), [
            'adjustment_date' => 'required'
        ],[
            'adjustment_date.required' => 'Stock Adjustment Date is required !'
        ]);
        if ($validator->fails()) {
            $error = $validator->errors();
            return response()->json([
                'message'   => $error,
                'code'  => 400
            ]);
        }
        $adjust             = $adjustment->store($request);

        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data'  => $adjust
        ]);
    }
    public function view($adjustment_id, StockadjustmentService $adjustment, StockadjustmentitemService $itmadjust)
    {
        $adjust                             = $adjustment->stockadjustRelations()
                                            ->select('sa.*')
                                            ->with('stores')
                                            ->where('sa.id', $adjustment_id)
                                            ->first();
        $adjustitem                         = $itmadjust->stockadjustitemRelation()
                                            ->select('*')
                                            ->with('stores')
                                            ->with('items')
                                            ->where('adjustment_id', $adjustment_id)
                                            ->get();

        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data'  => [
                'adjustment_stock' => $adjust,
                'adjustment_stock_item' => $adjustitem
            ]
        ]);
    }
    public function update(Request $request, StockadjustmentService $adjustment)
    {
        $validator = \Validator::make($request->all(), [
            'adjustment_date' => 'required'
        ],[
            'adjustment_date.required' => 'Stock Adjustment Date is required !'
        ]);
        if ($validator->fails()) {
            $error = $validator->errors();
            return response()->json([
                'message'   => $error,
                'code'  => 400
            ]);
        }
        $adjust             = $adjustment->update($request);

        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data'  => $adjust
        ]);
    }
    public function destroy(Request $request, StockadjustmentService $adjustment)
    {
        $validator = \Validator::make($request->all(), [
            'adjust_id' => 'required'
        ],[
            'adjust_id.required' => 'No data selected !'
        ]);
        if ($validator->fails()) {
            $error = $validator->errors();
            return response()->json([
                'message'   => $error,
                'code'  => 400
            ]);
        }
        return $adjustment->deleteMaster($request->adjust_id);
    }
}
