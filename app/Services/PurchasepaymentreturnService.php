<?php

namespace App\Services;

use App\Models\PurchasepaymentreturnModel;
/**
 * Class PurchasepaymentreturnService.
 */
class PurchasepaymentreturnService
{
    public function paymentreturnSql()
    {
        return PurchasepaymentreturnModel::from('db_purchasepaymentsreturn as payreturn');
    }
}
