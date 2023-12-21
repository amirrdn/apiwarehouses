<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockadjustmentModel extends Model
{
    use HasFactory;
    protected $table        = 'db_stockadjustment';
    protected $fillable     = ['id', 'store_id', 'warehouse_id', 'reference_no', 'adjustment_date', 'adjustment_note', 'created_date',
                                'created_time', 'created_by', 'system_ip', 'system_name', 'status'];
    public $timestamps      = false;

    public function stores()
    {
        return $this->belongsToMany(StoreModel::class, 'db_stockadjustment', 'id', 'store_id');
    }
}
