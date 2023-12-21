<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryModel extends Model
{
    use HasFactory;
    protected $table    = 'db_category';
    protected $fillable = ['id', 'store_id', 'count_id', 'category_id', 'category_name', 'description', 'company_id', 'status'];
    public $timestamps  = false;
}
