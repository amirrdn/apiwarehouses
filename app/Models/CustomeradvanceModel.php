<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomeradvanceModel extends Model
{
    use HasFactory;

    protected $table        = 'db_custadvance as cdv';
    protected $fillable     = ['id', 'store_id', 'count_id', 'payment_code', 'payment_date', 'customer_id', 'amount',
                                'payment_type', 'note', 'created_by', 'created_date', 'created_time', 'system_ip',
                                'system_name', 'status'];
    public $timestamps      = false;
}
