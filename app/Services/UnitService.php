<?php

namespace App\Services;
use App\Models\UnitModel;
/**
 * Class UnitService.
 */
class UnitService
{
    public function UnitRelation($leftjoin = false, $innerjoin = false)
    {
        $units                  = UnitModel::from('db_units as u');
        if($leftjoin){
            $units              = $units->leftjoin('db_store as st', 'u.store_id', 'st.id');
        }else if($innerjoin){
            $units              = $units->join('db_store as st', 'u.store_id', 'st.id');
        }

        return $units;
    }
}
