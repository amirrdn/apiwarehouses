<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseitemModel extends Model
{
    use HasFactory;

    protected $table        = 'db_warehouseitems';
    protected $fillabel     = ['id', 'store_id', 'warehouse_id', 'item_id', 'available_qty'];
    public $timestamps      = false;
}
