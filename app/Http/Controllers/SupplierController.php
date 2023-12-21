<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\SupplierService;
use App\Services\RolesService;

Use Exception;

class SupplierController extends Controller
{
    public function __construct(RolesService $rls) {
        $this->access = \Auth::user() ? strtoupper($rls->getRoleByusers(\Auth::user()->role_id)) == strtoupper('store admin') : false;
    }
    public function supplierList(Request $request, SupplierService $suppliers)
    {
        $supplier                   = $suppliers->supplierRelations()
                                    ->with('countries')
                                    ->with('states')
                                    ->with('supplier_store');
        if($this->access){
            $supplier               = $supplier->where('sp.store_id', \Auth::user()->store_id);
        }
        if(!empty($request->query_search)){
            $query_search           = $request->query_search;
            $supplier               = $supplier->where(function($query) use ($query_search){
                                        $query->where('sp.supplier_name', 'LIKE', '%'.$query_search.'%')
                                        ->orWhere('sp.email', 'LIKE', '%'.$query_search.'%')
                                        ->orWhere('sp.mobile', 'LIKE', '%'.$query_search.'%');
                                    });
        }
        $supplier                   = $supplier->select('sp.*');
        $supplier                   = $supplier->groupBy('sp.id');
        if(!empty($request->sort) && !empty($request->order)){
            $supplier               = $supplier->orderBy($request->order, $request->sort);
        }else{
            $supplier               = $supplier->orderBy('sp.id', 'asc');
        }
        if(!empty($request->per_page)){
            $supplier               = $supplier->paginate($request->per_page);
        }else{
            $supplier               = $supplier->get(); 
        }

        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data'  => $supplier
        ]);
    }
    public function store(Request $request, SupplierService $suppliers)
    {
        try{
            $validator = \Validator::make($request->all(), [
                'supplier_name' => 'required',
            ],[
                'supplier_name.required' => 'Supplier name is required !'
            ]);
            if ($validator->fails()) {
                $error = $validator->errors();
                return response()->json([
                    'message'   => $error,
                    'code'  => 400
                ]);
            }
            return $suppliers->store($request);
        } catch(Exception $e) {
            return response()->json([
                'message'   => $e->getMessage()
            ], 400);
        }
    }
    public function view($supplier_id, SupplierService $suppliers)
    {
        $supplier                       = $suppliers->supplierRelations()
                                        ->select('sp.*')
                                        ->with('countries')
                                        ->with('states')
                                        ->where('sp.id', $supplier_id)
                                        ->first();

        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data'  => $supplier
        ]);
    }
    public function update(Request $request, SupplierService $suppliers)
    {
        try{
            $validator = \Validator::make($request->all(), [
                'supplier_name' => 'required',
            ],[
                'supplier_name.required' => 'Supplier name is required !'
            ]);
            if ($validator->fails()) {
                $error = $validator->errors();
                return response()->json([
                    'message'   => $error,
                    'code'  => 400
                ]);
            }
            return $suppliers->update($request);
        } catch(Exception $e) {
            return response()->json([
                'message'   => $e->getMessage()
            ], 400);
        }
    }
    public function destory(Request $request, SupplierService $suppliers)
    {
        try{
            $validator = \Validator::make($request->all(), [
                'supplier_id' => 'required',
            ],[
                'supplier_id.required' => 'No data selected !'
            ]);
            if ($validator->fails()) {
                $error = $validator->errors();
                return response()->json([
                    'message'   => $error,
                    'code'  => 400
                ]);
            }
            return $suppliers->delete($request->supplier_id);
        } catch(Exception $e) {
            return response()->json([
                'message'   => $e->getMessage()
            ], 400);
        }
    }
}
