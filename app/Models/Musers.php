<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Musers extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table    = 'db_users';
    protected $fillable = [
        'username',
        'email',
        'password',
        'store_id'
    ];
    public $timestamps = false;
    protected $hidden = [
        'password', 'remember_token',
    ];
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function getAuthPassword() {
        return $this->password;
    }
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }
    public function userstore()
    {
        return $this->belongsToMany(StoreModel::class, 'db_users', 'id', 'store_id');
    }
}
