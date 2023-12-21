<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UnitService;
Use Exception;

class UnitController extends Controller
{
    public function getUnits(UnitService $units)
    {
        try{
            $unit                       = $units->UnitRelation(true, false);
            if(!empty(request()->unit_name)){
                $search                 = request()->unit_name;
                $unit                   = $unit->whereRaw('upper(unit_name) LIKE "%'.strtoupper($search).'%"');
            }
            $unit                       = $unit->where('u.status', 1)
                                        ->select('u.*', 'st.store_name');
            if(!empty(request()->order) && !empty(request()->sort)){
                $unit                   = $unit->orderBy(request()->order, request()->sort);
            }else{
                $unit                   = $unit->orderBy('unit_name', 'asc');
            }
            if(!empty(request()->per_page)){
                $unit                   = $unit->paginate(request()->per_page);
            }else{
                $unit                   = $unit->get();
            }
    
            return response()->json([
                'message'   => 'success',
                'code'  => 200,
                'data'  => $unit
            ]);
        } catch(Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
