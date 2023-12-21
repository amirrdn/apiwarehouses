<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CustomerService;
use App\Services\RolesService;
use App\Services\FilesService;
use App\Services\ShippingaddressService;
Use Exception;
class CustomerController extends Controller
{
    public function __construct(RolesService $rls) {
        $this->access = \Auth::user() ? strtoupper($rls->getRoleByusers(\Auth::user()->role_id)) == strtoupper('store admin') : false;
    }
    public function customerList(Request $request, CustomerService $cust)
    {
        try{
            if($this->access){
                $customer               = $cust->customerRelations(false, true);
            }else{
                $customer               = $cust->customerRelations(true, false);
            }
            $customer                   = $customer
                                        ->with('shippingaddress')
                                        ->with('countryship')
                                        ->with('customer_store');
            if(!empty(request()->query_search)){
                $search_customer        = request()->query_search;
                $customer               = $customer->where(function($query) use ($search_customer){
                                            $query->whereRaw('upper(customer_name) LIKE "%'.strtoupper($search_customer).'%"')
                                            ->orWhereRaw('upper(cust.mobile) LIKE "%'.strtoupper($search_customer).'%"');
                                        });
            }
            $customer                   = $customer
                                        ->select('cust.*');
            if(!empty($request->order) && !empty($request->sort)){
                $customer               = $customer->orderBy($request->order, $request->sort);
            }else{
                $customer               = $customer->orderBy('customer_name', 'desc');
            }
            if(!empty(request()->per_page)){
                $customer               = $customer->paginate(request()->per_page);
            }else{
                $customer               = $customer->get();
            }
            
            return response()->json([
                'message'   => 'success',
                'code'  => 200,
                'data'  => $customer
            ]);
        } catch(Exception $e) {
            return response()->json([
                'message'   => $e->getMessage()
            ], 400);
        }
    }
    public function store(Request $request, CustomerService $cust, FilesService $uploads)
    {
        $validator = \Validator::make($request->all(), [
            'customer_name' => 'required',
        ],[
            'customer_name.required' => 'Customer name is required !'
        ]);
        if ($validator->fails()) {
            $error = $validator->errors();
            return response()->json([
                'message'   => $error,
                'code'  => 400
            ]);
        }
        $filesdb = '';
        if($request->has('files')){
            request()->merge([
                'name'  => $request->customer_name
            ]);
            $filesdb    = $uploads->UploadFiles($request);
        }
        request()->merge([
            'attachment_1' => $filesdb
        ]);
        $custinsert = $cust->store($request);

        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data'  => $custinsert
        ]);
    }
    public function view($customer_id, CustomerService $cust, ShippingaddressService $ship)
    {
        if($this->access){
            $customer               = $cust->customerRelations(false, true);
        }else{
            $customer               = $cust->customerRelations(true, false);
        }
        $customer                   = $customer
                                    
                                    ->where('cust.id', $customer_id)
                                    ->select('cust.*')
                                    ->first();
        $shipping                   = (new ShippingaddressService())->ShippRelations()
                                    ->where('shp.customer_id', $customer->id)
                                    ->select('shp.*')
                                    ->first();
        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data'  => [
                'customer' => $customer,
                'shipping'  => $shipping
            ]
            ]);
    }
    public function updatecustomer(Request $request, CustomerService $cust, FilesService $uploads)
    {
        $validator = \Validator::make($request->all(), [
            'customer_name' => 'required',
        ],[
            'customer_name.required' => 'Customer name is required !'
        ]);
        if ($validator->fails()) {
            $error = $validator->errors();
            return response()->json([
                'message'   => $error,
                'code'  => 400
            ]);
        }
        $filesdb = '';
        $ffiles = $request['files_image'];
        if (stripos($ffiles, "data:image/") !== false) {
            if($request->has('files_image')){
                $request->merge([
                    'name'  => $request->customer_name
                ]);
                $filesdb    = $uploads->UploadFiles($request);
            }
        }else{
            $filesdb        = $request['files_image'];
        }
        $request->merge([
            'attachment_1' => $filesdb
        ]);
        $custinsert = $cust->update($request);

        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data'  => $custinsert
        ]);
    }
    public function destroy(Request $request, CustomerService $cust)
    {
        $validator = \Validator::make($request->all(), [
            'customer_id' => 'required',
        ],[
            'customer_id.required' => 'No data selected !'
        ]);
        if ($validator->fails()) {
            $error = $validator->errors();
            return response()->json([
                'message'   => $error,
                'code'  => 400
            ]);
        }
        return $cust->delete($request->customer_id);
    }
}
