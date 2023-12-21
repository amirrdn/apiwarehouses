<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesModel extends Model
{
    use HasFactory;
    protected $table        = 'db_sales';
    protected $fillabel     = ['id', 'store_id', 'warehouse_id', 'init_code', 'count_id', 'sales_code', 'reference_no',
                            'sales_date', 'due_date', 'sales_status', 'customer_id', 'other_charges_input', 'other_charges_tax_id',
                            'other_charges_amt', 'discount_to_all_input', 'discount_to_all_type', 'tot_discount_to_all_amt',
                            'subtotal', 'round_off', 'grand_total', 'sales_note', 'payment_status', 'paid_amount', 'created_date',
                            'created_time', 'created_by', 'system_ip', 'system_name', 'company_id', 'pos', 'status', 'return_bit',
                            'customer_previous_due', 'customer_total_due', 'quotation_id', 'coupon_id', 'coupon_amt', 'invoice_terms'];
    public $timestamps      = false;

    public function quots(): BelongsToMany
    {
        return $this->belongsToMany(QuotationModel::class, 'role_user');
    }
}
