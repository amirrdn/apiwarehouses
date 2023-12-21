<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountransactionModel extends Model
{
    use HasFactory;
    
    protected $table    = 'ac_transactions';
    protected $fillable = ['id', 'store_id', 'payment_code', 'transaction_date', 'transaction_type', 'debit_account_id',
                        'credit_account_id', 'debit_amt', 'credit_amt', 'note', 'created_by', 'created_date', 'ref_accounts_id',
                        'ref_moneytransfer_id', 'ref_moneydeposits_id', 'ref_salespayments_id', 'ref_salespaymentsreturn_id',
                        'ref_purchasepayments_id', 'ref_purchasepaymentsreturn_id', 'ref_expense_id', 'customer_id', 'supplier_id',
                        'short_code'];
    public $timestamps  = false;
}
