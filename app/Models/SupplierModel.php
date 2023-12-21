<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierModel extends Model
{
    use HasFactory;

    protected $table        = 'db_suppliers';
    protected $fillable     = ['id', 'store_id', 'count_id', 'supplier_code', 'supplier_name', 'mobile', 'phone',
                                'email', 'gstin', 'tax_number', 'vatin', 'opening_balance', 'purchase_due', 'purchase_return_due',
                                'country_id', 'state_id', 'city', 'postcode', 'address', 'system_ip', 'system_name', 'created_date',
                                'created_time', 'created_by', 'company_id', 'status'];
    public $timestamps      = false;

    public function countries()
    {
        return $this->belongsToMany(CountryModel::class, 'db_suppliers', 'id', 'country_id');
    }
    public function states()
    {
        return $this->belongsToMany(StateModel::class, 'db_suppliers', 'id', 'state_id');
    }
    public function supplier_store()
    {
        return $this->belongsToMany(StoreModel::class, 'db_suppliers', 'id', 'store_id');
    }
}
