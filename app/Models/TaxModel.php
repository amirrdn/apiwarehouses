<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxModel extends Model
{
    use HasFactory;
    protected $table    = 'db_tax';
    protected $fillable = ['id', 'store_id', 'tax_name', 'tax', 'group_bit', 'subtax_ids', 'status'];
    public $timestamps  = false;
}
