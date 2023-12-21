<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TwilioModel extends Model
{
    use HasFactory;

    protected $table    = 'db_twilio';
    protected $fillable = ['id', 'store_id', 'account_sid', 'auth_token', 'twilio_phone', 'status'];
    public $timestamps  = false;
}
