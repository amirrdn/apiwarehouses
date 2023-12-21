<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemsModel extends Model
{
    use HasFactory;
    protected $table    = 'db_items';
    protected $fillable = ['id', 'store_id', 'count_id', 'item_code', 'item_name', 'category_id', 'sku', 'hsn', 'sac', 'unit_id', 'alert_qty',
                            'brand_id', 'lot_number', 'expire_date', 'price', 'tax_id', 'purchase_price', 'tax_type', 'profit_margin', 'sales_price', 
                            'stock', 'item_image', 'system_ip', 'system_name', 'created_date', 'created_time', 'created_by', 'company_id', 'status', 
                            'discount_type', 'discount', 'service_bit', 'seller_points', 'custom_barcode', 'description', 'item_group', 'parent_id', 
                            'variant_id', 'child_bit', 'mrp'];
    public $timestamps  = false;

    public function itemUser()
    {
        return $this->belongsToMany(Musers::class, 'db_items', 'db_users.username', 'created_bya');
    }
}
