<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WarehouseService;
use App\Services\RolesService;
Use Exception;

class WarehouseController extends Controller
{
    private RolesService $rls;
    public function __construct(RolesService $rls) {
        $this->access = \Auth::user() ? strtoupper($rls->getRoleByusers(\Auth::user()->role_id)) == strtoupper('store admin') : false;
    }
    public function warehouseList(WarehouseService $wh)
    {
        try{
            if($this->access){
                $warehouse                  = $wh->WarehouseRelations(false, true);
            }else{
                $warehouse                  = $wh->WarehouseRelations(true, false);
            }
            if(!empty(request()->query_search)){
                $warehouse_search           = request()->query_search;
                $warehouse                  = $warehouse->where(function($query) use ($warehouse_search){
                                                $query->whereRaw('upper(warehouse_name) LIKE "%'.strtoupper($warehouse_search).'%"')
                                                ->orWhereRaw('upper(w.mobile) LIKE "%'.strtoupper($warehouse_search).'%"');
                                            });
            }
            $warehouse                      = $warehouse->select('w.*')->orderBy('warehouse_name', 'asc');
            if(!empty(request()->per_page)){
                $warehouse                  = $warehouse->paginate(request()->per_page);
            }else{
                $warehouse                  = $warehouse->get();
            }
    
            return response()->json([
                'message'   => 'success',
                'code'  => 200,
                'data'  => $warehouse
            ]);
        } catch(Exception $e) {
            return response()->json([
                'message'   => $e->getMessage()
            ], 400);
        }
    }
}
