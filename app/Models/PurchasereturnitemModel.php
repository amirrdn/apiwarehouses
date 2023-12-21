<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasereturnitemModel extends Model
{
    use HasFactory;

    protected $table    = 'db_purchaseitemsreturn';
    protected $fillable = ['store_id', 'purchase_id', 'return_id', 'return_status', 'item_id', 'return_qty', 'price_per_unit', 'tax_id',
                        'tax_amt', 'tax_type', 'discount_input', 'discount_amt', 'discount_type', 'unit_total_cost', 'total_cost', 'profit_margin_per',
                        'unit_sales_price', 'status', 'description'];
    public $timestamps  = false;

    public function items()
    {
        return $this->belongsToMany(ItemsModel::class, 'db_purchaseitemsreturn', 'id', 'item_id');
    }
    public function taxs()
    {
        return $this->belongsToMany(TaxModel::class, 'db_purchaseitemsreturn', 'id', 'tax_id');
    }
}
