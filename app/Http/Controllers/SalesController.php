<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SaleService;
use App\Services\RolesService;
use App\Services\SalesitemService;
use App\Services\SalespaymentService;

class SalesController extends Controller
{
    private RolesService $rls;
    public function __construct(RolesService $rls) {
        $this->access = \Auth::user() ? strtoupper($rls->getRoleByusers(\Auth::user()->role_id)) == strtoupper('store admin') : false;
    }
    public function totalSales(SaleService $sl, RolesService $role)
    {
        // $seller_points              = $sl->SalesRelations()
        //                             ->join('db_salesitems as si', 's.id', 'si.sales_id')
        //                             ->where('s.created_by', \Auth::user()->username)
        //                             ->where('s.store_id', \Auth::user()->store_id)
        //                             ->selectRaw('coalesce(sum(si.seller_points)) as seller_points')
        //                             ->groupBy('s.id')
        //                             ->first();

        $total_invoice              = $sl->SalesRelations();
        $checkadmin                 = strtoupper($role->getRoleByusers(\Auth::user()->role_id)) == strtoupper('store admin');
        if($checkadmin){
            $total_invoice          = $total_invoice->where('s.created_by', \Auth::user()->username);
        }
        $total_invoice              = $total_invoice->where('s.store_id', \Auth::user()->store_id)
                                    ->selectRaw('COALESCE(COUNT(s.id), 0) as total')->first();

        $total_invoice_amount       = $sl->SalesRelations()
                                    ->where('store_id', \Auth::user()->store_id)
                                    ->selectRaw('COALESCE(sum(grand_total),0) AS tot_sal_grand_total')
                                    ->first();
        $paidmount                  = $sl->SalesRelations();
        if($checkadmin){
            $paidmount              = $paidmount->where('created_by', \Auth::user()->username);
        }
        $tot_received_amount        = $paidmount->selectRaw("COALESCE(SUM(paid_amount),0) AS paid_amount")->where('store_id', \Auth::user()->store_id)->first();
        $sales_due_total            = \DB::table('db_customers')
                                    ->selectRaw("COALESCE(SUM(sales_due),0) AS sales_due");
        if($checkadmin){
            if(\Auth::user()->role_id != 2){
                $sales_due_total    = $sales_due_total->where('created_by', \Auth::user()->username);
            }
        }
        $sales_due_total            = $sales_due_total->where('store_id', \Auth::user()->store_id)
                                    ->first();
       
        return response()->json([
            'message'   => 'success',
            'code'  => 200,
             'data' => array(
            'total_invoice' => $total_invoice->total,
            'tot_sal_grand_total' => $total_invoice_amount->tot_sal_grand_total,
            'paid_amount' => $tot_received_amount->paid_amount,
            'sales_due_total' => $sales_due_total->sales_due
             )
        ]);
    }
    public function listSales(Request $request, SaleService $sl)
    {
        $listsales                      = $sl->SalesRelations()
                                        ->leftjoin('db_customers as cs', 's.customer_id', 'cs.id');
        if(!empty($request->customer_id) && $request->customer_id != null){
            $listsales                  = $listsales->where('s.customer_id', $request->customer_id);
        }
        if($this->access){
            $listsales                  = $listsales->where('s.store_id', \Auth::user()->store_id);
        }
        if(!empty($request->start_date) && !empty($request->end_date)){
            $listsales                  = $listsales->whereBetween('s.sales_date', [date('Y-m-d', strtotime($request->start_date)),  date('Y-m-d', strtotime($request->end_date. '+ 1 day'))]);
        }
        if(!empty($request->users)){
            $listsales                  = $listsales->where('s.created_by', $request->users);
        }
        $listsales                      = $listsales
                                        ->select('s.*', 'cs.customer_name',
                                        \DB::raw("
                                        CASE
                                            WHEN DATEDIFF(CURDATE(),STR_TO_DATE(due_date, '%Y-%m-%d')) > 0
                                            THEN CONCAT(DATEDIFF( CURDATE( ), STR_TO_DATE( due_date, '%Y-%m-%d' ) ), ' days overdue')
                                            ELSE CONCAT(abs(DATEDIFF( CURDATE( ), STR_TO_DATE( due_date, '%Y-%m-%d' ) )), ' days Left')
                                        END as date_difference
                                        "))
                                        ->groupBy('s.id')
                                        ->orderBy('s.sales_code', 'DESC');
        if(!empty($request->per_page)){
            $listsales                  = $listsales->paginate(request()->per_page);
        }else{
            $listsales                  = $listsales->get();
        }

        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data'  => $listsales
        ]);
    }
    public function store(Request $request, SaleService $sl)
    {
        return $sl->store($request);
    }
    public function view($sales_id, SaleService $sl, SalesitemService $sitem, SalespaymentService $pay)
    {
        $sales                          = $sl->SalesRelations()
                                        ->join('db_customers as cs', 's.customer_id', 'cs.id')
                                        ->leftjoin('db_country as cn', 'cs.country_id', 'cn.id')
                                        ->leftjoin('db_states as sts', 'cs.state_id', 'sts.id')
                                        ->leftjoin('db_customer_coupons as cp', 's.coupon_id', 'cp.id')
                                        ->leftjoin('db_shippingaddress as shp', 'cs.id', 'shp.customer_id')
                                        ->where('s.id', $sales_id);
        if($this->access){
            $sales                      = $sales
                                        ->where('s.store_id', \Auth::user()->store_id);
        }
        $sales                          = $sales
                                        ->selectRaw("s.coupon_id,s.coupon_amt, s.due_date,s.quotation_id,s.store_id,cs.customer_name,cs.mobile,cs.phone,cs.gstin,cs.tax_number,cs.email,cs.shippingaddress_id,cs.id,
                                        cs.opening_balance,cs.country_id,cs.state_id,cs.city,
                                        cs.postcode,cs.address,s.sales_date,s.created_time,s.reference_no,
                                        s.sales_code,s.sales_status,s.sales_note,s.invoice_terms,
                                        coalesce(s.grand_total,0) as grand_total,
                                        coalesce(s.subtotal,0) as subtotal,
                                        coalesce(s.paid_amount,0) as paid_amount,
                                        coalesce(s.other_charges_input,0) as other_charges_input,
                                        s.other_charges_tax_id,
                                        coalesce(s.other_charges_amt,0) as other_charges_amt,
                                        s.discount_to_all_input,
                                        s.discount_to_all_type,
                                        coalesce(s.tot_discount_to_all_amt,0) as tot_discount_to_all_amt,
                                        coalesce(s.round_off,0) as round_off,
                                        s.payment_status,s.pos, cn.country, sts.state, cp.code, cp.value, cp.type,
                                        st.store_name, st.mobile as store_mobile, st.phone as phone_store, st.city as city_store,
                                        st.address as address_store, st.gst_no as gst_no_store, st.vat_no as vat_no_store, st.pan_no as pan_no_store,
                                        st.email as email_store, shp.city as shipping_city, shp.postcode as shipping_postcode, shp.address as shipping_address, s.id as sales_id,
                                        s.customer_id, cs.address as customer_address")
                                        ->groupBy('s.id')
                                        ->first();
        $salesitem                      = $sitem->SalesitemRelations()
                                        ->where('sitm.sales_id', $sales_id)
                                        ->selectRaw("sitm.description,itm.mrp, itm.item_name, sitm.sales_qty,sitm.tax_type,
                                        sitm.price_per_unit, tx.tax,tx.tax_name,sitm.tax_amt,
                                        sitm.discount_input,sitm.discount_amt, sitm.unit_total_cost,
                                        sitm.total_cost , un.unit_name, itm.sku, itm.hsn, sitm.id as sales_item_id, sitm.item_id,
                                        tx.tax as unit_tax, sitm.description, tx.id as tax_id, sitm.discount_type, itm.item_image")
                                        ->get();
        if($this->access){
            $paymentsales               = $pay->salesPaymentRelations(false, true);
        }else{
            $paymentsales               = $pay->salesPaymentRelations(true, false);
        }
        $paymentsales                   = $paymentsales->where('py.sales_id', $sales_id)
                                        ->select('py.*', 'ac.account_name')
                                        ->get();
        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data'  => [
                'sales' => $sales,
                'sales_item' => $salesitem,
                'sales_payment' => $paymentsales
            ]
        ]);
    }
    public function update(Request $request,SaleService $sl)
    {
        return $sl->update($request);
    }
    public function destroy(Request $request, SaleService $sl)
    {
        return $sl->deleteSales($request);
    }
}
