<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesitemsModel extends Model
{
    use HasFactory;

    protected $table        = 'db_salesitems';
    protected $fillable     = ['id', 'store_id', 'sales_id', 'sales_status', 'item_id', 'description', 'sales_qty',
                                'price_per_unit', 'tax_type', 'tax_id', 'tax_amt', 'discount_type', 'discount_input',
                                'discount_amt', 'unit_total_cost', 'total_cost', 'status', 'seller_points', 'purchase_price'];
    public $timestamps      = false;
}
