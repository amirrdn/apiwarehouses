<?php

namespace App\Services;

use App\Models\CountryModel;
use App\Models\StateModel;
/**
 * Class AdministrativeService.
 */
class AdministrativeService
{
    public function sqlCountry()
    {
        return CountryModel::query();
    }
    public function Statesql()
    {
        return StateModel::query();
    }
}
