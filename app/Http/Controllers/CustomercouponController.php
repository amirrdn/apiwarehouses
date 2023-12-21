<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\CustomercoupunService;
Use Exception;
class CustomercouponController extends Controller
{
    public function Couponlist(Request $request, CustomercoupunService $cp)
    {
        try{
            $coupon                 = $cp->relatonCustomercuoupon();
            if(!empty($request->query_search)){
                $query_search       = $request->query_search;
    
                $coupon             = $coupon->where(function($query) use ($query_search){
                                        $query->whereRaw('upper(name) LIKE "%'.strtoupper($query_search).'%"')
                                        ->orWhereRaw('upper(code) LIKE "%'.strtoupper($query_search).'%"');
                                    });
            }
            $coupon                 = $coupon->with('couponstore')
                                    ->with('customercoupon')
                                    ->with('coupon');
    
            if(!empty($request->order) && !empty($request->sort)){
                $coupon             = $coupon->orderBy($request->order, $request->sort);
            }else{
                $coupon             = $coupon->orderBy('name', 'asc');
            }
            if(!empty($request->per_page)){
                $coupon             = $coupon->paginate($request->per_page);
            }else{
                $coupon             = $coupon->get();
            }
    
    
            return response()->json([
                'message'   => 'success',
                'code'  => 200,
                'data'  => $coupon
            ]);
        } catch(Exception $e) {
            return response()->json([
                'message'   => $e->getMessage()
            ], 400);
        }
    }
    public function store(Request $request, CustomercoupunService $cp)
    {
        try{
            $validator = \Validator::make($request->all(), [
                'coupon_id' => 'required',
                'code' => 'required'
            ],[
                'coupon_id.required' => 'The Coupon Name is required !',
                'code.required' => 'Coupon Code is required !'
            ]);
    
            if ($validator->fails()) {
                $error = $validator->errors();
                return response()->json([
                    'message'   => $error,
                    'code'  => 400
                ]);
            }
    
            $coupon                 = $cp->store($request);
    
            return response()->json([
                'message'   => 'success ',
                'code'  => 200,
                'data'  => $coupon
            ]);
        } catch(Exception $e) {
            return response()->json([
                'message'   => $e->getMessage()
            ], 400);
        }
    }
    public function view(Request $request, CustomercoupunService $cp)
    {
        try{
            $coupon                 = $cp->relatonCustomercuoupon()
                                    ->with('couponstore')
                                    ->with('customercoupon')
                                    ->with('coupon')
                                    ->find($request->customer_coupon_id);
            return response()->json([
                'message'   => 'success',
                'code'  => 200,
                'data'  => $coupon
            ]);
        } catch(Exception $e) {
            return response()->json([
                'message'   => $e->getMessage()
            ], 400);
        }
    }
    public function update(Request $request, CustomercoupunService $cp)
    {
        try{
            $validator = \Validator::make($request->all(), [
                'customer_coupon_id' => 'required',
                'coupon_id' => 'required',
                'code' => 'required'
            ],[
                'customer_coupon_id.required' => 'The Coupon ID is required !',
                'coupon_id.required' => 'The Coupon Name is required !',
                'code.required' => 'Coupon Code is required !'
            ]);
    
            if ($validator->fails()) {
                $error = $validator->errors();
                return response()->json([
                    'message'   => $error,
                    'code'  => 400
                ]);
            }
            
            $coupon                 = $cp->update($request);
            
            return response()->json([
                'message'   => 'success ',
                'code'  => 200,
                'data'  => $coupon
            ]);
        } catch(Exception $e) {
            return response()->json([
                'message'   => $e->getMessage()
            ], 400);
        }
    }
    public function destroy(Request $request, CustomercoupunService $cp)
    {
        try{
            $validator = \Validator::make($request->all(), [
                'customer_coupon_id' => 'required',
            ],[
                'customer_coupon_id.required' => 'The Coupon ID is required !',
            ]);
    
            if ($validator->fails()) {
                $error = $validator->errors();
                return response()->json([
                    'message'   => $error,
                    'code'  => 400
                ]);
            }
            return $cp->delete($request->customer_coupon_id);
        } catch(Exception $e) {
            return response()->json([
                'message'   => $e->getMessage()
            ], 400);
        }
    }
}
