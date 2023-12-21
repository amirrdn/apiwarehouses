<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TaxService;
Use Exception;

class TaxController extends Controller
{
    public function GetTaxs(TaxService $tx)
    {
        try{
            $taxs                   = $tx->TaxRelation(false, true);
            if(request()->query_search){
                $search             = request()->query_search;
                $taxs               = $taxs->where(\DB::raw('lower(tx.tax_name)','LIKE', '%'.strtolower($search).'%'));
            }
            if(!empty(request()->tax_status)){
                $taxs               = $taxs->where('tx.status', request()->tax_status);
            }
            $taxs                   = $taxs->select('tx.*', 'st.store_name');
            if(!empty(request()->order) && !empty(request()->sort)){
                $taxs               = $taxs->orderBy(request()->order, request()->sort);
            }else{
                $taxs               = $taxs->orderBy('tax_name', 'asc');
            }
            if(!empty(request()->per_page)){
                $taxs               = $taxs->paginate(request()->per_pate);
            }else{
                $taxs               = $taxs->get();
            }
    
            return response()->json([
                'message'   => 'success',
                'code'      => 200,
                'data'      => $taxs
            ]);
        } catch(Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
