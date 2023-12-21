<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationitemsModel extends Model
{
    use HasFactory;

    protected $table    = 'db_quotationitems';
    protected $fillable = ['id', 'store_id', 'quotation_id', 'quotation_status', 'item_id', 'description', 'quotation_qty', 'price_per_unit',
                            'tax_type', 'tax_id', 'tax_amt', 'discount_type', 'discount_input', 'discount_amt', 'unit_total_cost', 'total_cost',
                            'status', 'seller_points'];
    public $timestamps  = false;

    public function items()
    {
        return $this->belongsToMany(ItemsModel::class, 'db_quotationitems', 'id', 'item_id');
    }
}
