<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountModel extends Model
{
    use HasFactory;

    protected $table        = 'ac_accounts';
    protected $fillable     = ['id', 'count_id', 'store_id', 'parent_id', 'sort_code', 'account_name', 'account_code',
                                'balance', 'note', 'created_by', 'created_date', 'created_time', 'system_ip', 'system_name',
                                'status', 'delete_bit', 'account_selection_name', 'paymenttypes_id', 'customer_id', 'supplier_id',
                                'expense_id'];
    public $timestamps      = false;
}
