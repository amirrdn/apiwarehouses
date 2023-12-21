<?php

namespace App\Services;

use App\Models\QuotationModel;
use App\Models\StoreModel;

use App\Services\ItemsService;
use App\Services\QuotationitemService;
use App\Services\StoreService;
/**
 * Class QuotationService.
 */
use Carbon\Carbon;
use Larinfo;
class QuotationService
{
    public function __construct(){
        $larinfo                        = Larinfo::getInfo();
        $this->date_now                 = Carbon::now()->format('Y-m-d');
        $this->ip                       = request()->ip();
        $this->info                     = $larinfo['server']['software']['os'];
        $this->timenow                  = Carbon::now()->format('h:i:s');
    }

    public function quotationSql()
    {
        return QuotationModel::from('db_quotation as qt')
        ->leftjoin('db_sales as s', 'qt.id', 's.quotation_id');
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
            $quot                           = new QuotationModel;
            
            $quot->store_id                 = \Auth::user()->store_id;
            $quot->warehouse_id             = $data->warehouse_id;
            $quot->count_id                 = QuotationModel::max('count_id') + 1;
            $storeinit                      = StoreModel::where('id', \Auth::user()->store_id)->select('quotation_init')->first();
            $quot->quotation_code           = $storeinit->quotation_init.$this->getCode();
            $quot->created_date             = $this->date_now;
            $quot->created_time             = $this->timenow;
            $quot->created_by               = \Auth::user()->username;
            $quot->system_ip                = $this->ip;
            $quot->system_name              = $this->info;
        }else if($data->action == 'update'){
            $quot                           = QuotationModel::find($data->quotation_id);
        }else{
            return response()->json([
                'message'   => 'error insert or update data',
                'code'  => 400
            ]);
        }
        $quot->reference_no                 = $data->reference_no;
        $quot->quotation_date               = $data->quotation_date ? date('Y-m-d', strtotime($data->quotation_date)) : date('Y-m-d');
        $quot->expire_date                  = $data->expire_date ? date('Y-m-d', strtotime($data->expire_date)) : null;
        $quot->quotation_status             = $data->quotation_status;
        $quot->customer_id                  = $data->customer_id;
        $quot->other_charges_input          = $data->other_charges_input;
        $quot->other_charges_tax_id         = $data->other_charges_tax_id;
        $quot->other_charges_amt            = $data->other_charges_amt;
        $quot->discount_to_all_input        = $data->discount_to_all_input;
        $quot->discount_to_all_type         = $data->discount_to_all_type;
        $quot->tot_discount_to_all_amt      = $data->tot_discount_to_all_amt;
        $quot->subtotal                     = $data->subtotal;
        $quot->round_off                    = $data->round_off;
        $quot->grand_total                  = $data->grand_total;
        $quot->quotation_note               = $data->quotation_note;
        $quot->payment_status               = $data->payment_status;
        $quot->paid_amount                  = $data->paid_amount;
        $quot->company_id                   = $data->company_id;
        $quot->pos                          = $data->pos;
        $quot->status                       = 1;
        $quot->return_bit                   = $data->return_bit;
        $quot->customer_previous_due        = $data->customer_previous_due;
        $quot->customer_total_due           = $data->customer_total_due;
        $quot->sales_status                 = $data->sales_status;

        $quot->save();
        $removepreviousitems                = \DB::table('db_quotationitems')
                                            ->where('quotation_id', $quot->id);
        if(count($removepreviousitems->get()) > 0){
            $removepreviousitems->delete();
        }
        if($data->items && count($data->items) > 0){

            foreach($data->items as $b){
                $discount_amt_per_unit = $b['discount_amt'] / $b['quotation_qty'];
                if($b['tax_type'] == 'Exclusive'){
                    $single_unit_total_cost = $b['price_per_unit'] + ($b['unit_tax'] * $b['price_per_unit'] / 100);
                }else{//Inclusive
                    $single_unit_total_cost = $b['price_per_unit'];
                }
    
                $single_unit_total_cost -=$discount_amt_per_unit;
    
                $data['quotation_id']       = $quot->id;
                $data['quotation_status']	= $quot->quotation_status; 
                $data['item_id'] 			= $b['item_id'];
                $data['description'] 		= $b['description']; 
                $data['quotation_qty']      = $b['quotation_qty'];
                $data['price_per_unit'] 	= $b['price_per_unit'];
                $data['tax_type'] 			= $b['tax_type'];
                $data['tax_id'] 			= $b['tax_id'];
                $data['tax_amt'] 			= $b['tax_amt'];
                $data['discount_input'] 	= $b['discount_input'];
                $data['discount_amt'] 		= $b['discount_amt'];
                $data['discount_type'] 	    = $b['discount_type'];
                $data['unit_total_cost'] 	= $single_unit_total_cost;
                $data['total_cost'] 		= $b['total_cost'];
                $data['status']	 		    = 1;
                $data['seller_points']		= (new ItemsService())->get_seller_points($b['item_id']) * $b['quotation_qty'];
                $data['action']             = isset($b['quotation_item_id']) ? $data->action : 'create';
                $data['quotation_item_id']  = isset($b['quotation_item_id']) ? $b['quotation_item_id'] : null;
                $data->action               = 'create';
    
                (new QuotationitemService())->insertOrupdate($data);
                (new ItemsService())->update_items_quantity($b['item_id'], $quot->warehouse_id, $quot->store_id);
            }
        }

        return $quot;
    }
    public function delete(array $quotation_id)
    {
        $converted_rec              = \DB::table('db_sales')
                                    ->whereIn('quotation_id', $quotation_id)
                                    ->get();
        if(count($converted_rec) > 0){
            $notif  = array();
            foreach($converted_rec as $b){
                $notif[] = $b->sales_code;
            }
            $notifstr = "Can't Delete!<br>These Quotations List Have the Sales Records! <br /> sales id : ".implode(", ", $notif);
            return response()->json([
                'message'   => $notifstr,
                'code'  => 400
            ]);
        }

        $dbquotations               = QuotationModel::whereIn('id', $quotation_id);

        if(count($dbquotations->get()) > 0){
            $dbquotations->delete();
        }else{
            return response()->json([
                'message'   => 'errorr delete',
                'code'  => 400
            ]);
        }

        return response()->json([
            'message'   => 'success insert',
            'code'  => 200
        ]);
    }
    public function getCode()
    {
        $code = (new StoreService())->get_count_id('db_quotation')->count_id - 1;
        $code++;
        return str_pad($code, 4, '0', STR_PAD_LEFT);
    }
}
