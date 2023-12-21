<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserwarehousesModel extends Model
{
    use HasFactory;

    protected $table    = 'db_userswarehouses';
    protected $fillable = ['id', 'user_id', 'warehouse_id'];
    public $timestamps  = false;
}
