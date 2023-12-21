<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\AdministrativeService;

class AdministrativeController extends Controller
{
    public function getcountryState()
    {
        $countries          = (new AdministrativeService())->sqlCountry()
                            ->orderBy('country', 'asc')
                            ->get();
        $state              = (new AdministrativeService())->Statesql()
                            ->orderBy('state', 'asc')
                            ->get();
        
        return response()->json([
            'message'   => 'success',
            'data'  => [
                'countries' => $countries,
                'states' => $state
            ]
            ]);
    }
}
