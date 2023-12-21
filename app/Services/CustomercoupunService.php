<?php

namespace App\Services;

use App\Models\CustomercouponModel;

use Carbon\Carbon;
use Larinfo;
/**
 * Class CustomercoupunService.
 */
class CustomercoupunService
{
    public function __construct(){
        $larinfo                        = Larinfo::getInfo();
        $this->date_now                 = Carbon::now()->format('Y-m-d');
        $this->ip                       = request()->ip();
        $this->info                     = $larinfo['server']['software']['os'];
        $this->timenow                  = Carbon::now()->format('h:i:s');
    }
    public function relatonCustomercuoupon()
    {
        return CustomercouponModel::query();
    }
    public function store(object $data)
    {
        $data['action']                 = 'create';
        return $this->insertOrupdate($data);
    }
    public function update(object $data)
    {
        $data['action']                 = 'update';
        return $this->insertOrupdate($data);
    }
    public function insertOrupdate(object $data)
    {
        if($data->action == 'create'){
            $checkcoupon                = CustomercouponModel::where(\DB::raw("upper(code)"), strtoupper($data->code))
                                        ->get();
            if(count($checkcoupon) > 0){
                return response()->json([
                    'message' => 'This Coupon Code already exists!!',
                    'code'  => 400
                ]);
            }
            $custcoupon                 = new CustomercouponModel;
            $custcoupon->store_id       = \Auth::user()->store_id;
            $custcoupon->created_date   = $this->date_now;
            $custcoupon->created_time   = $this->timenow;
            $custcoupon->created_by     = \Auth::user()->username;
            $custcoupon->system_ip      = $this->ip;
            $custcoupon->system_name    = $this->info;
        }else{
            $custcoupon                 = CustomercouponModel::find($data->customer_coupon_id);
        }
        $custcoupon->code               = $data->code;
        $custcoupon->name               = $data->name;
        $custcoupon->description        = $data->description;
        $custcoupon->value              = $data->value;
        $custcoupon->type               = $data->type;
        $custcoupon->expire_date        = $data->expire_date ? date('Y-m-d', strtotime($data->expire_date)) : date('Y-m-d');
        $custcoupon->status             = 1;
        $custcoupon->customer_id        = $data->customer_id;
        $custcoupon->coupon_id          = $data->coupon_id;

        $custcoupon->save();

        return $custcoupon;
    }
    public function delete(array $customer_coupon_id)
    {
        $checkcouponsales               = \DB::table('db_sales')
                                        ->whereIn('coupon_id', $customer_coupon_id)
                                        ->get();
        if(count($checkcouponsales) > 0){
            $salescode = array();
            foreach($checkcouponsales as $b){
                $salescode[] = $b->sales_code;
            }
            return response()->json([
                'message'   => "Can't Delete!! This Coupon Already Used in Sales invoice! <br /> sales code: ".implode(', ', $salescode),
                'code'  => 400
            ]);
        }
        request()->merge([
            'role_id' => \Auth::user()->role_id
        ]);
        $dbcouponcust                   = CustomercouponModel::whereIn('id', $customer_coupon_id);
        if(count($dbcouponcust->get()) > 0){
            $dbcouponcust->delete();
            return response()->json([
                'message' => 'success delete',
                'code'  => 200
            ]);
        }else{
            return response()->json([
                'message'   => 'error delete',
                'code'  => 400
            ]);
        }
    }
}
