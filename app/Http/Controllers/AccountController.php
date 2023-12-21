<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AccountService;
use App\Services\RolesService;

class AccountController extends Controller
{
    public function __construct() {
        $this->access = \Auth::user() ? strtoupper((new RolesService())->getRoleByusers(\Auth::user()->role_id)) == strtoupper('store admin') : false;
    }
    public function accountList(AccountService $ac)
    {
        if($this->access){
            $account                    = $ac->accountRelations(false, true);
        }else{
            $account                    = $ac->accountRelations(true, false);
        }
        $account                        = $account->where('ac.status', 1);
        if(!empty(request()->query_search)){
            $query_search               = request()->query_search;
            $account                    = $account->where(function($query) use ($query_search){
                                            $query->whereRaw('upper(ac.account_name) LIKE "%'.strtoupper($query_search).'%"')
                                            ->orWhereRaw('upper(cs.customer_name) LIKE "%'.strtoupper($query_search).'%"');
                                        });
        }
        $account                        = $account->select('ac.*', 'cs.customer_name');
        if(!empty($request->order) && !empty($request->sort)){
            $account                    = $account->orderBy($request->order, $request->sort);
        }else{
            $account                    = $account->orderBy('ac.account_name', 'desc');
        }
        if(!empty(request()->per_page)){
            $account                    = $account->paginate(request()->per_page);
        }else{
            $account                    = $account->get();
        }

        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data'  => $account
        ]);
    }
}
