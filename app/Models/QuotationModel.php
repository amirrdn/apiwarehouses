<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationModel extends Model
{
    use HasFactory;

    protected $table    = 'db_quotation';
    protected $fillable = ['id', 'store_id', 'warehouse_id', 'count_id', 'quotation_code', 'reference_no', 'quotation_date', 'expire_date',
                            'quotation_status', 'customer_id', 'other_charges_input', 'other_charges_tax_id', 'other_charges_amt', 'discount_to_all_input',
                            'discount_to_all_type', 'tot_discount_to_all_amt', 'subtotal', 'round_off', 'grand_total', 'quotation_note', 'payment_status',
                            'paid_amount', 'created_date', 'created_time', 'created_by', 'system_ip', 'system_name', 'company_id', 'pos', 'status', 'return_bit',
                            'customer_previous_due', 'customer_total_due', 'sales_status'];
    public $timestamps  = false;

    public function customers()
    {
        return $this->belongsToMany(CustomerModel::class, 'db_quotation', 'id', 'customer_id');
    }
    public function storequotation()
    {
        return $this->belongsToMany(StoreModel::class, 'db_quotation', 'id', 'store_id');
    }
    public function quotationwarehouse()
    {
        return $this->belongsToMany(WarehouseModel::class, 'db_quotation', 'id', 'warehouse_id');
    }

}
