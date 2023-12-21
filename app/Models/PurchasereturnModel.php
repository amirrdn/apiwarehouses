<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasereturnModel extends Model
{
    use HasFactory;

    protected $table        = 'db_purchasereturn';
    protected $fillable     = ['id', 'store_id', 'count_id', 'warehouse_id', 'purchase_id', 'return_code', 'reference_no', 'return_date',
                            'return_status', 'supplier_id', 'other_charges_input', 'other_charges_tax_id', 'other_charges_amt', 'discount_to_all_input',
                            'discount_to_all_type', 'tot_discount_to_all_amt', 'subtotal', 'round_off', 'grand_total', 'return_note', 'payment_status',
                            'paid_amount', 'created_date', 'created_time', 'created_by', 'system_ip', 'system_name', 'company_id', 'status'];
    public $timestamps      = false;

    public function purchases()
    {
        return $this->belongsToMany(PurchaseModel::class, 'db_purchasereturn', 'id', 'purchase_id');
    }
    public function suppliers()
    {
        return $this->belongsToMany(SupplierModel::class, 'db_purchasereturn', 'id', 'supplier_id');
    }
    public function stores()
    {
        return $this->belongsToMany(StoreModel::class, 'db_purchasereturn', 'id', 'store_id');
    }
    public function pupayment()
    {
        return $this->belongsToMany(PurchasepaymentreturnModel::class, 'db_purchasereturn', 'id', 'return_id');
    }
}
