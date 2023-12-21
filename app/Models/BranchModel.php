<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchModel extends Model
{
    use HasFactory;
    protected $table    = 'db_brands';
    protected $fillable = ['id', 'store_id', 'brand_code', 'brand_name', 'description', 'status'];
    public $timestamps  = false;
}
