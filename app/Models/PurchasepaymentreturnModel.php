<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasepaymentreturnModel extends Model
{
    use HasFactory;

    protected $table            = 'db_purchasepaymentsreturn';
    protected $fillabel         = ['id', 'count_id', 'payment_code', 'store_id', 'purchase_id', 'return_id', 'payment_date',
                                    'payment_type', 'payment', 'payment_note', 'system_ip', 'system_name', 'created_time', 'created_date',
                                    'created_by', 'status', 'account_id', 'supplier_id', 'short_code'];
    public $timestamps          = false;

    public function accounts()
    {
        return $this->belongsToMany(AccountModel::class, 'db_purchasepaymentsreturn', 'id', 'account_id');
    }
    public function suppliers()
    {
        return $this->belongsToMany(SupplierModel::class, 'db_purchasepaymentsreturn', 'id', 'supplier_id');
    }
    public function purchases()
    {
        return $this->belongsToMany(PurchaseModel::class, 'db_purchasepaymentsreturn', 'id', 'purchase_id');
    }
}
