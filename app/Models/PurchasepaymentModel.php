<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasepaymentModel extends Model
{
    use HasFactory;

    protected $table        = 'db_purchasepayments';
    protected $fillable     = ['id', 'count_id', 'payment_code', 'store_id', 'purchase_id', 'payment_date',
                                'payment_type', 'payment', 'payment_note', 'system_ip', 'system_name', 'created_time',
                                'created_date', 'created_by', 'status', 'account_id', 'supplier_id', 'short_code'];
    public $timestamps      = false;

    public function accounts()
    {
        return $this->belongsToMany(AccountModel::class, 'db_purchasepayments', 'id', 'account_id');
    }
}
