<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsapiModel extends Model
{
    use HasFactory;

    protected $table        = 'db_smsapi';
    protected $fillable     = ['id', 'store_id', 'info', 'key', 'key_value', 'delete_bit'];
    public $timestamps      = false;
}
