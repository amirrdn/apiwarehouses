<?php

namespace App\Services;
use App\Models\TaxModel;
/**
 * Class TaxService.
 */
class TaxService
{
    public function TaxRelation($leftjoin = false, $innerjoin = false)
    {
        $tax                    = TaxModel::from('db_tax as tx');
        if($leftjoin){
            $tax                = $tax->leftjoin('db_store as st', 'tx.store_id', 'st.id');
        }else if($innerjoin){
            $tax                = $tax->join('db_store as st', 'tx.store_id', 'st.id');
        }

        return $tax;
    }
}
