<?php

namespace App\Services;
use App\Models\BranchModel;
/**
 * Class BranchService.
 */
class BranchService
{
    public function BranchRelelation($leftjoin = false, $innerjoin = false)
    {
        $branchs                = BranchModel::from('db_brands as b');
        if($leftjoin){
            $branchs            = $branchs->leftjoin('db_store as st', 'b.store_id', 'st.id');
        }else if($innerjoin){
            $branchs            = $branchs->join('db_store as st', 'b.store_id', 'st.id');
        }

        return $branchs;
    }
}
