<?php

namespace App\Services;

use Illuminate\Http\Request;

use App\Services\RolesService;
use App\Services\StoreService;
use App\Services\CustomService;
/**
 * Class ChartServices.
 */
class ChartServices
{
    public function __construct() {
        $this->access = \Auth::user() ? strtoupper((new RolesService())->getRoleByusers(\Auth::user()->role_id)) == strtoupper('store admin') : false;
    }
    public function barchart()
    {
        $bar_chart      = array();
        for ($i=6; $i >= 0; $i--){
            $bar_chart['date'][$i]  = date("Y-m-d",strtotime("-".$i." months"));
            $bar_chart['_month'][$i]  = date("m",strtotime("".$i." months"));
            $bar_chart['month'][$i] = date("M",strtotime($bar_chart['date'][$i])).",".date("Y",strtotime($bar_chart['date'][$i]));

            $dbpurchase             = \DB::table('db_purchase')
                                    ->whereMonth('purchase_date', date("m",strtotime($bar_chart['date'][$i])))
                                    ->whereYear('purchase_date', date("Y",strtotime($bar_chart['date'][$i])));
            if($this->access){
                $dbpurchase         = $dbpurchase->where('created_by', \Auth::user()->username);
            }
            $dbpurchase             = $dbpurchase
                                    ->selectRaw("COALESCE(SUM(grand_total),0) AS pur_total")
                                    ->first();

            $bar_chart['purchase'][$i] = $dbpurchase->pur_total;

            $dbsales                = \DB::table('db_sales')
                                    ->whereMonth('sales_date', date("m",strtotime($bar_chart['date'][$i])))
                                    ->whereYear('sales_date', date("Y",strtotime($bar_chart['date'][$i])))
                                    ->where('sales_status', 'Final');
            if($this->access){
                $dbsales            = $dbsales->where('created_by', \Auth::user()->username);
            }
            $dbsales                = $dbsales
                                    ->selectRaw("COALESCE(SUM(grand_total),0) AS sal_total")
                                    ->first();

            $bar_chart['sales'][$i] = $dbsales->sal_total;

            $dbexpense              = \DB::table('db_expense')
                                    ->whereMonth('expense_date', date("m",strtotime($bar_chart['date'][$i])))
                                    ->whereYear('expense_date', date("Y",strtotime($bar_chart['date'][$i])));
            if($this->access){
                $dbexpense          = $dbexpense->where('created_by', \Auth::user()->username);
            }
            $dbexpense              = $dbexpense
                                    ->selectRaw("COALESCE(SUM(expense_amt),0) AS expense_amt")
                                    ->first();
            $bar_chart['expense'][$i]= $dbexpense->expense_amt;
        }

        return $bar_chart;
    }
    public function get_pie_chart($value = NULL)
    {

        $dbitems                    = \DB::table('db_items AS a')
                                    ->join('db_salesitems AS b', 'a.id', 'b.item_id')
                                    ->join('db_sales AS c', 'b.sales_id', 'c.id');
        if($this->access){
            $dbitems                = $dbitems->where('a.store_id', \Auth::user()->store_id);
        }
        $dbitems                    = $dbitems
                                    ->selectRaw("COALESCE(SUM(b.sales_qty),0) AS sales_qty, a.item_name")
                                    ->groupBy('a.id')
                                    ->limit(10)
                                    ->orderBy('sales_qty', 'asc')
                                    ->get();

        $pie_chart  = array();
        $i = 0;
        if(count($dbitems) > 0){
            foreach($dbitems as $key => $b){
                if($b->sales_qty > 0){
                    $i++;
                    $pie_chart []= array(
                        'name'  => $b->item_name,
                        'sales_qty' => $b->sales_qty
                    );
                    // $pie_chart['tranding_item'][$i]['name']       = $b->item_name;
                  	// $pie_chart['tranding_item'][$i]['sales_qty']  = $b->sales_qty;
                }
            }
        }
        // $pie_chart['tranding_item'][$i + 1]['tot_rec'] = $i;
        return $pie_chart;
    }
    public function get_storewise_details()
    {
        $storeusers                 = (new StoreService())->getStoreByid(\Auth::user()->store_id);
        $currencies                 = (new CustomService())->parseBlob($storeusers->currency);

        $dbstore                    = \DB::table('db_store')->select('*')->get();

        $tbody                      = '';
        if(count($dbstore) > 0){
            foreach($dbstore as $key => $st){
                $dbsales                = \DB::table('db_sales')
                                        ->where('sales_status', 'Final')
                                        ->where('store_id', $st->id)
                                        ->selectRaw("COALESCE(sum(grand_total),0) AS tot_sal_grand_total")
                                        ->first();
                $sal_total              = $dbsales->tot_sal_grand_total;
    
                $dbsalesdue             = \DB::table('db_sales')
                                        ->where('sales_status', 'Final')
                                        ->where('store_id', $st->id)
                                        ->selectRaw("COALESCE(sum(grand_total),0)-COALESCE(sum(paid_amount),0) AS sales_due_total")
                                        ->first();
                $sales_due_total        = $dbsalesdue->sales_due_total;
    
                $dbexpense              = \DB::table('db_expense')
                                        ->where('store_id', $st->id)
                                        ->selectRaw("COALESCE(SUM(expense_amt),0) AS exp_total")
                                        ->first();
                $exp_total              = $dbexpense->exp_total;
    
                $tbody  .= "<tr>";
                $tbody  .= "<td>".($key + 1)."</td>";
                $tbody  .= "<td>".$st->store_name."</td>";
                $tbody  .= "<td>".$currencies.' '.(new CustomService())->store_number_format($sal_total)."</td>";
                $tbody  .= "<td>".$currencies.' '.(new CustomService())->store_number_format($exp_total)."</td>";
                $tbody  .= "<td>".$currencies.' '.(new CustomService())->store_number_format($sales_due_total)."</td>";
                $tbody  .= "</tr>";
            }
        }
        return $tbody;
    }
    public function RecentItems()
    {
        
        $dbitems                    = \DB::table('db_items')
                                    ->where('status', 1);
        if($this->access){
            $dbitems                = $dbitems->where('store_id', \Auth::user()->store_id);
        }
        $dbitems                    = $dbitems
                                    ->select('item_name', 'sales_price')
                                    ->orderBy('id', 'desc')
                                    ->limit(10)
                                    ->get();
        return $dbitems;
    }
    public function recentSalesInvoice()
    {

        $dbsales                    = \DB::table('db_sales as s')
                                    ->leftjoin('db_customers as c', 's.customer_id', 'c.id');
        if($this->access){
            $dbsales                = $dbsales
                                    ->where('created_by', \Auth::user()->username)
                                    ->where('store_id', \Auth::user()->store_id);
        }
        $dbsales                    = $dbsales
                                    ->select('s.*', 'c.customer_name')
                                    ->orderBy('s.id', 'desc')
                                    ->limit(10)
                                    ->get();
        return $dbsales;
    }
    public function dashobarDetail(Request $request)
    {
        $storeusers                 = (new StoreService())->getStoreByid(\Auth::user()->store_id);
        $currencies                 = (new CustomService())->parseBlob($storeusers->currency);
        $info   = array();

        $dbsuppliers                = \DB::table('db_suppliers')
                                    ->where('status', 1);
        if($this->access){
            $dbsuppliers            = $dbsuppliers->where('store_id', \Auth::user()->store_id);
        }
        $dbsuppliers                = $dbsuppliers
                                    ->selectRaw("coalesce(count(*),0) as tot_sup")
                                    ->first();
        $tot_sup                    = $dbsuppliers->tot_sup;
        $info['tot_sup']            = $tot_sup;

        $dbitems                    = \DB::table('db_items')
                                    ->where('status', 1);
        if($this->access){
            $dbitems                = $dbitems->where('store_id', \Auth::user()->store_id);
        }
        $dbitems                    = $dbitems
                                    ->selectRaw("coalesce(count(*),0) as tot_pro")
                                    ->first();
        $tot_pro                    = $dbitems->tot_pro;
        $info['tot_pro']            = $tot_pro;

        $dbcust                     = \DB::table('db_customers')
                                    ->where('status', 1);
        if($this->access){
            $dbcust                 = $dbcust->where('store_id', \Auth::user()->store_id);
        }
        $dbcust                     = $dbcust
                                    ->selectRaw("coalesce(count(*),0) as tot_cust")
                                    ->first();
        $tot_cust                   = $dbcust->tot_cust;
        $info['tot_cust']           = $tot_cust;

        $dbpu                       = \DB::table('db_purchase')
                                    ->where('status', 1);
        if($this->access){
            $dbpu                   = $dbpu->where('store_id', \Auth::user()->store_id);
        }
        if($request->dates == 'Today'){
            $dbpu                   = $dbpu->where('purchase_date', date('Y-m-d', strtotime('+1 day')));
        }else if($request->dates == 'Weekly'){
            $dbpu                   = $dbpu->whereRaw("purchase_date > DATE_SUB(NOW(), INTERVAL 1 WEEK)");
        }else if($request->dates == 'Monthly'){
            $dbpu                   = $dbpu->whereRaw("purchase_date> DATE_SUB(NOW(), INTERVAL 1 MONTH)");
        }else if($request->dates == 'Yearly'){
            $dbpu                   = $dbpu->whereRaw('"Y-m-d H:i:s" >= DATE_ADD(purchase_date, INTERVAL 1 YEAR)');
        }
        $dbpu                       = $dbpu
                                    ->selectRaw("coalesce(count(*),0) as tot_pur")
                                    ->first();
        $tot_pur                    = $dbpu->tot_pur;
        $info['tot_pur']            = $tot_pur;

        $dbsales                    = \DB::table('db_sales')
                                    ->where('sales_status', 'Final');
        if($this->access){
            $dbsales                = $dbsales->where('store_id', \Auth::user()->store_id);
        }
        if($request->dates == 'Today'){
            $dbsales                = $dbsales->where('sales_date', date('Y-m-d', strtotime('+1 day')));
        }else if($request->dates == 'Weekly'){
            $dbsales                = $dbsales->whereRaw("sales_date > DATE_SUB(NOW(), INTERVAL 1 WEEK)");
        }else if($request->dates == 'Monthly'){
            $dbsales                = $dbsales->whereRaw("sales_date> DATE_SUB(NOW(), INTERVAL 1 MONTH)");
        }else if($request->dates == 'Yearly'){
            $dbsales                = $dbsales->whereRaw("sales_date DATE_SUB(NOW(), INTERVAL 1 YEAR)");
        }
        $dbsales                    = $dbsales
                                    ->selectRaw("coalesce(count(*),0) as tot_sal")
                                    ->first();
        $tot_sal                    = $dbsales->tot_sal;
        $info['tot_sal']            = $tot_sal;

        $dbreturnsales              = \DB::table('db_salesreturn');
        if($this->access){
            $dbreturnsales          = $dbreturnsales->where('store_id', \Auth::user()->store_id);
        }
        if($request->dates == 'Today'){
            $dbreturnsales          = $dbreturnsales->where('return_date', date('Y-m-d', strtotime('+1 day')));
        }else if($request->dates == 'Weekly'){
            $dbreturnsales          = $dbreturnsales->whereRaw("return_date > DATE_SUB(NOW(), INTERVAL 1 WEEK)");
        }else if($request->dates == 'Monthly'){
            $dbreturnsales          = $dbreturnsales->whereRaw("return_date > DATE_SUB(NOW(), INTERVAL 1 MONTH)");
        }else if($request->dates == 'Yearly'){
            $dbreturnsales          = $dbreturnsales->whereRaw("return_date > DATE_SUB(NOW(), INTERVAL 1 YEAR)");
        }
        $dbreturnsales              = $dbreturnsales
                                    ->selectRaw("COALESCE(sum(grand_total),0) AS tot_sal_ret_grand_total")
                                    ->first();
        $tot_sal_ret_grand_total    = $dbreturnsales->tot_sal_ret_grand_total;
        $info['tot_sal_ret_grand_total']    = $currencies.' '.(new CustomService())->kmb($tot_sal_ret_grand_total);

        $dbsales2                   = \DB::table('db_sales')
                                    ->where('sales_status', 'Final');
        if($this->access){
            $dbsales2               = $dbsales2->where('store_id', \Auth::user()->store_id);
        }
        if($request->dates == 'Today'){
            $dbsales2               = $dbsales2->where('sales_date', date('Y-m-d', strtotime('+1 day')));
        }else if($request->dates == 'Weekly'){
            $dbsales2               = $dbsales2->whereRaw("sales_date > DATE_SUB(NOW(), INTERVAL 1 WEEK)");
        }else if($request->dates == 'Monthly'){
            $dbsales2               = $dbsales2->whereRaw("sales_date > DATE_SUB(NOW(), INTERVAL 1 MONTH)");
        }else if($request->dates == 'Yearly'){
            $dbsales2               = $dbsales2->whereRaw("sales_date > DATE_SUB(NOW(), INTERVAL 1 YEAR)");
        }
        $dbsales2                   = $dbsales2
                                    ->selectRaw("COALESCE(sum(grand_total),0) AS tot_sal_grand_total")
                                    ->first();
        $tot_sal_grand_total        = $dbsales2->tot_sal_grand_total;
        $info['tot_sal_grand_total']= $currencies.' '.(new CustomService())->kmb($tot_sal_grand_total);

        $dbexpense                  = \DB::table('db_expense');
        if($this->access){
            $dbexpense              = $dbexpense->where('store_id', \Auth::user()->store_id);
        }
        if($request->dates == 'Today'){
            $dbexpense              = $dbexpense->where('expense_date', date('Y-m-d', strtotime('+1 day')));
        }else if($request->dates == 'Weekly'){
            $dbexpense              = $dbexpense->whereRaw("expense_date > DATE_SUB(NOW(), INTERVAL 1 WEEK)");
        }else if($request->dates == 'Monthly'){
            $dbexpense              = $dbexpense->whereRaw("expense_date > DATE_SUB(NOW(), INTERVAL 1 MONTH)");
        }else if($request->dates == 'Yearly'){
            $dbexpense              = $dbexpense->whereRaw("expense_date > DATE_SUB(NOW(), INTERVAL 1 YEAR)");
        }
        $dbexpense                  = $dbexpense
                                    ->selectRaw("COALESCE(sum(expense_amt),0) AS tot_exp")
                                    ->first();
        $tot_exp                    = $dbexpense->tot_exp;
        $info['tot_exp']            = $currencies.' '.(new CustomService())->kmb($tot_exp);

        $dbsales3                   = \DB::table('db_sales')
                                    ->where('sales_status', 'Final');
        if($this->access){
            $dbsales3               = $dbsales3->where('store_id', \Auth::user()->store_id);
        }
        if($request->dates == 'Today'){
            $dbsales3               = $dbsales3->where('sales_date', date('Y-m-d', strtotime('+1 day')));
        }else if($request->dates == 'Weekly'){
            $dbsales3               = $dbsales3->whereRaw("sales_date > DATE_SUB(NOW(), INTERVAL 1 WEEK)");
        }else if($request->dates == 'Monthly'){
            $dbsales3               = $dbsales3->whereRaw("sales_date > DATE_SUB(NOW(), INTERVAL 1 MONTH)");
        }else if($request->dates == 'Yearly'){
            $dbsales3               = $dbsales3->whereRaw("sales_date > DATE_SUB(NOW(), INTERVAL 1 YEAR)");
        }
        $dbsales3                   = $dbsales3
                                    ->selectRaw("(COALESCE(sum(grand_total),0)-COALESCE(sum(paid_amount),0)) as sales_due")
                                    ->first();
        $sales_due                  = $dbsales3->sales_due;
        $info['sales_due']          = $currencies.' '.(new CustomService())->kmb($sales_due);

        $dbpu1                      = \DB::table('db_purchase')
                                    ->where('purchase_status', 'Recieved');
        if($this->access){
            $dbpu1                  = $dbpu1->where('store_id', \Auth::user()->store_id);
        }
        if($request->dates == 'Today'){
            $dbpu1                  = $dbpu1->where('purchase_date', date('Y-m-d', strtotime('+1 day')));
        }else if($request->dates == 'Weekly'){
            $dbpu1                  = $dbpu1->whereRaw("purchase_date > DATE_SUB(NOW(), INTERVAL 1 WEEK)");
        }else if($request->dates == 'Monthly'){
            $dbpu1                  = $dbpu1->whereRaw("purchase_date > DATE_SUB(NOW(), INTERVAL 1 MONTH)");
        }else if($request->dates == 'Yearly'){
            $dbpu1                  = $dbpu1->whereRaw("purchase_date > DATE_SUB(NOW(), INTERVAL 1 YEAR)");
        }
        $dbpu1                      = $dbpu1
                                    ->selectRaw("(COALESCE(sum(grand_total),0)-COALESCE(sum(paid_amount),0)) as purchase_due")
                                    ->first();
        $purchase_due               = $dbpu1->purchase_due;
        $info['purchase_due']       = $currencies.' '.(new CustomService())->kmb($purchase_due);

        return $info;
    }
    
}
