<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountryModel extends Model
{
    use HasFactory;

    protected $table    = 'db_country';
    protected $fillable = ['id', 'country', 'added_on', 'status'];
    public $timestamps  = false;
}
