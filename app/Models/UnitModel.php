<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitModel extends Model
{
    use HasFactory;
    protected $table    = 'db_units';
    protected $fillable = ['id', 'store_id', 'unit_name', 'description', 'company_id', 'status'];
    public $timestamps  = false;
}
