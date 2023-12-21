<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\ChartServices;
use App\Services\RolesService;
use App\Services\ItemsService;

class Warehousesalescontroller extends Controller
{
    public function __construct() {
        $this->access = \Auth::user() ? strtoupper((new RolesService())->getRoleByusers(\Auth::user()->role_id)) == strtoupper('store admin') : false;
    }

    public function getTotalawrehouse()
    {
        $sales                          = (new ChartServices())->barchart();

        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data' => [$sales]
        ]);
    }
    public function storeWise()
    {
        $sales                          = (new ChartServices())->get_storewise_details();
        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data' => $sales
        ]);
    }
    public function recentItems()
    {
        $items                          = (new ChartServices())->RecentItems();
        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data' => $items
        ]);
    }
    public function recentSalesInvoice()
    {
        $sales                          = (new ChartServices())->recentSalesInvoice();
        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data' => $sales
        ]);
    }
    public function detaildashobardWarehouse(Request $request)
    {
        $sales                          = (new ChartServices())->dashobarDetail($request);
        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data' => $sales
        ]);
    }
    public function stockAlert()
    {
        $dbitems                    = (new ItemsService())->itemsRelation();
        if($this->access){
            $dbitems                = $dbitems->where('itm.store_id', \Auth::user()->store_id);
        }
        $dbitems                    = $dbitems
                                    ->whereRaw("(itm.stock <= itm.alert_qty or itm.stock is null)")
                                    ->where('itm.service_bit', 0)
                                    ->where('itm.status', 1)
                                    ->select('itm.*', 'c.category_name', 'br.brand_name');
        if(!empty(request()->per_page)){
            $dbitems                = $dbitems->paginate(request()->per_page);
        }else{
            $dbitems                = $dbitems->get();
        }

        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data'  => $dbitems
        ]);
    }
    public function trendingItems()
    {
        $trending               = (new ChartServices())->get_pie_chart();

        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data'  => $trending
        ]);
    }
}
