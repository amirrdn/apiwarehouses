<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseitemsModel extends Model
{
    use HasFactory;

    protected $table        = 'db_purchaseitems';
    protected $fillable     = ['id', 'store_id', 'purchase_id', 'purchase_status', 'item_id', 'purchase_qty', 'price_per_unit',
                            'tax_type', 'tax_id', 'tax_amt', 'discount_type', 'discount_input', 'discount_amt', 'unit_total_cost',
                            'total_cost', 'profit_margin_per', 'unit_sales_price', 'status', 'description'];
    public $timestamps      = false;

    public function items()
    {
        return $this->belongsToMany(ItemsModel::class, 'db_purchaseitems', 'id', 'item_id');
    }
    public function taxs()
    {
        return $this->belongsToMany(TaxModel::class, 'db_purchaseitems', 'id', 'tax_id');
    }
}
