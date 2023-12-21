<?php

namespace App\Services;
use App\Models\AccountModel;
/**
 * Class AccountService.
 */
class AccountService
{
    public function accountRelations($leftjoin = false, $innerjoin = false)
    {
        $account                    = AccountModel::from('ac_accounts as ac')
                                    ->leftjoin('db_paymenttypes as paytp', 'ac.paymenttypes_id', 'paytp.id')
                                    ->leftjoin('db_customers as cs', 'ac.customer_id', 'cs.id')
                                    ->leftjoin('db_suppliers as sup', 'ac.supplier_id', 'sup.id')
                                    ->leftjoin('db_expense as ex', 'ac.expense_id', 'ex.id');;
        if($leftjoin){
            $account                = $account->leftjoin('db_store as st', 'ac.store_id', 'st.id');
        }else{
            $account                = $account->join('db_store as st', 'ac.store_id', 'st.id');
        }
        return $account;
    }
}
