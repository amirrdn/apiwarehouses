<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionModel extends Model
{
    use HasFactory;
    protected $table        = 'db_permissions';
    protected $fillable     = ['id', 'store_id', 'role_id', 'permissions'];
    public $timestamps      = false;
}
