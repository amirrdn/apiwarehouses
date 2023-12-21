<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreModel extends Model
{
    use HasFactory;

    protected $table        = 'db_store';
    protected $fillable     = ['id', 'store_code', 'store_name', 'store_website', 'mobile', 'phone', 'email',
                            'website', 'store_logo', 'logo', 'upi_id', 'upi_code', 'country', 'state', 'city',
                            'address', 'postcode', 'gst_no', 'vat_no', 'pan_no', 'bank_details', 'cid', 'category_init',
                            'item_init', 'supplier_init', 'purchase_init', 'purchase_return_init', 'customer_init', 'sales_init',
                            'sales_return_init', 'expense_init', 'accounts_init', 'journal_init', 'cust_advance_init', 'invoice_view',
                            'sms_status', 'status', 'language_id', 'currency_id', 'currency_placement', 'timezone', 'date_format', 'time_format',
                            'sales_discount', 'currencysymbol_id', 'regno_key', 'fav_icon', 'purchase_code', 'change_return', 'sales_invoice_format_id',
                            'pos_invoice_format_id', 'sales_invoice_footer_text', 'round_off', 'created_date', 'created_time', 'created_by', 'created_by',
                            'system_name', 'quotation_init', 'decimals', 'money_transfer_init', 'sales_payment_init', 'sales_return_payment_init', 'purchase_payment_init',
                            'purchase_return_payment_init', 'expense_payment_init', 'current_subscriptionlist_id', 'smtp_host', 'smtp_port',
                            'smtp_user', 'smtp_pass', 'smtp_status', 'sms_url', 'user_id', 'mrp_column', 'qty_decimals', 'signature',
                            'show_signature', 'invoice_terms', 'previous_balance_bit', 't_and_c_status', 't_and_c_status_pos',
                            'number_to_words', 'default_account_id'];
    public $timestamps      = false;
}
