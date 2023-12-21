<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalespaymentModel extends Model
{
    use HasFactory;

    protected $table        = 'db_salespayments';
    protected $fillable     = ['id', 'count_id', 'payment_code', 'store_id', 'sales_id', 'payment_date', 'payment_type',
                                'payment', 'payment_note', 'change_return', 'system_ip', 'system_name', 'created_time',
                                'created_date', 'created_by', 'status', 'account_id', 'customer_id', 'short_code', 'advance_adjusted',
                                'cheque_number', 'cheque_period', 'cheque_status'];
    public $timestamps      = false;
}
