<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseModel extends Model
{
    use HasFactory;

    protected $table    = 'db_warehouse';
    protected $fillable = ['id', 'store_id', 'warehouse_type', 'warehouse_name', 'mobile', 'email', 'status', 'created_date'];
    public $timestamps  = false;
}
