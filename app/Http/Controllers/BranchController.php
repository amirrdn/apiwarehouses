<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BranchService;
Use Exception;

class BranchController extends Controller
{
    public function getBranch(Request $request, BranchService $brnch)
    {
        try{
            $branch                 = $brnch->BranchRelelation();
            if(!empty($request->get('search_branch'))){
                $search_branch      = $request->search_branch;
                $branch             = $branch->where(function($query) use ($search_branch){
                                        $query->whereRaw('upper(brand_name) LIKE "%'.strtoupper($search_branch).'%"')
                                        ->orWhereRaw('upper(brand_code) LIKE "%'.strtoupper($search_branch).'%"');
                                    });
            }
            if(!empty($request->order) && !empty($request->sort)){
                $branch             = $branch->orderBy($request->order, $request->sort);
            }
            $branch                 = $branch->select('b.*');
            if(!empty($request->per_page)){
                $branch             = $branch->paginate(request()->per_page);
            }else{
                $branch             = $branch->get();
            }
    
            return response()->json([
                'message'   => 'success',
                'code'  => 200,
                'data'  => $branch
            ]);
        }catch(Exception $e){
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
