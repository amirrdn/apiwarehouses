<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StateModel extends Model
{
    use HasFactory;

    protected $table        = 'db_states';
    protected $fillable     = ['id', 'store_id', 'state_code', 'state', 'country_code', 'country_id', 'country', 'added_on', 'company_id', 'status'];
    public $timestamps      = false;
}
