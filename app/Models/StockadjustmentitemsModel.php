<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockadjustmentitemsModel extends Model
{
    use HasFactory;
    protected $table        = 'db_stockadjustmentitems';
    protected $fillable     = ['id', 'store_id', 'warehouse_id', 'adjustment_id', 'item_id', 'adjustment_qty', 'status', 'description'];
    public $timestamps      = false;

    public function stores()
    {
        return $this->belongsToMany(StoreModel::class, 'db_stockadjustmentitems', 'id', 'store_id');
    }
    public function items()
    {
        return $this->belongsToMany(ItemsModel::class, 'db_stockadjustmentitems', 'id', 'item_id');
    }
}
