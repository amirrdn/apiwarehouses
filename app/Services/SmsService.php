<?php

namespace App\Services;
use App\Models\StoreModel;
use App\Services\CustomService;
use App\Services\SmsapiService;

/**
 * Class SmsService.
 */
class SmsService
{
    public function send_sms_using_template($data_id,$template_id)
    {
        $template_name          = '';
		if($template_id == 1){
			$template_name      = 'GREETING TO CUSTOMER ON SALES';
		}
		if($template_id == 2){
			$template_name      = 'GREETING TO CUSTOMER ON SALES RETURN';
		}
        
        $dbtemplate             = \DB::table('db_smstemplates')
                                ->where('template_name', $template_name)
                                ->where('status', 1)
                                ->first();
        $content                = $dbtemplate->content;

        if(!empty($content)){
            switch ($template_id) {
                case 1;

                /* SALES SMS */

                    $dbsales        = \DB::table('db_sales as a')
                                    ->join('db_customers as b', 'a.customer_id', 'b.id')
                                    ->where('a.id', $data_id)
                                    ->selectRaw("a.store_id,a.customer_id,b.customer_name,b.mobile,a.sales_code,
                                    a.sales_date,a.grand_total,a.paid_amount, a.store_id");
                    if(count($dbsales->get()) > 0){
                        $q2         = $dbsales->first();
                        $storeusers         = StoreModel::where('id', $q2->store_id)->first();
                        $currencies         = (new CustomService())->parseBlob($storeusers->currency);
                        $content            = str_replace("{{customer_name}}", $q2->customer_name, $content);
                        $content            = str_replace("{{sales_id}}", $q2->sales_code, $content);
                        $content            = str_replace("{{sales_date}}", date('d-m-Y', strtotime($q2->sales_date)), $content);
                        $content            = str_replace("{{sales_amount}}", $currencies.' '.number_format($q2->grand_total,2,'.',''),$content);
                        $content            = str_replace("{{paid_amt}}", $currencies.' '.number_format($q2->paid_amount,2,'.',''), $content);
                        $content            = str_replace("{{due_amt}}",$currencies.' '.number_format($q2->grand_total-$q2->paid_amount,2,'.',''), $content);

                        /*Find Company Details*/
                        $q3                 = $storeusers;

                        /*Insert/Replace into Content*/
                        $content            = str_replace("{{store_name}}", $q3->store_name, $content);
                        $content            = str_replace("{{store_mobile}}", $q3->mobile, $content);
                        $content            = str_replace("{{store_address}}", $q3->address, $content);
                        $content            = str_replace("{{store_website}}", $q3->store_website, $content);
                        $content            = str_replace("{{store_email}}", $q3->email, $content);

                        request()->merge([
                            'mobile' => $q2->mobile,
                            'message'   => $content
                        ]);
                        return (new SmsapiService())->sendSMS(request());
                    }
                    break;
                case 2:

                    /* SALES RETURN SMS */
                    $dbsalesreturn      = \DB::table('db_salesreturn as a')
                                        ->join('db_customers as b', 'a.customer_id', 'b.id')
                                        ->where('a.id', $data_id)
                                        ->selectRaw("a.store_id,a.customer_id,b.customer_name,b.mobile,a.return_code,
                                        a.return_date,a.grand_total,a.paid_amount, a.store_id");
                    if(count($dbsalesreturn->get()) > 0){
                        $q2                 = $dbsalesreturn->first();
                        $storeusers         = StoreModel::where('id', $q2->store_id)->first();
                        $currencies         = (new CustomService())->parseBlob($storeusers->currency);

                        $content            = str_replace("{{customer_name}}", $q2->customer_name, $content);
						$content            = str_replace("{{return_id}}", $q2->return_code, $content);
						$content            = str_replace("{{return_date}}", date('d-m-Y', strtotime($q2->return_date)), $content);
						$content            = str_replace("{{return_amount}}", $currencies.' '.number_format($q2->grand_total,2,'.',''), $content);
						$content            = str_replace("{{paid_amt}}", $currencies.' '.number_format($q2->paid_amount,2,'.',''), $content);
						$content            = str_replace("{{due_amt}}",$currencies.' '.number_format($q2->grand_total-$q2->paid_amount,2,'.',''), $content);

                        $q3                 = $storeusers;

                        $content            = str_replace("{{store_name}}", $q3->store_name, $content);
						$content            = str_replace("{{store_mobile}}", $q3->mobile, $content);
						$content            = str_replace("{{store_address}}", $q3->address, $content);
						$content            = str_replace("{{store_website}}", $q3->store_website, $content);
						$content            = str_replace("{{store_email}}", $q3->email, $content);

                        request()->merge([
                            'mobile' => $q2->mobile,
                            'message'   => $content
                        ]);
                        return (new SmsapiService())->sendSMS(request());
                    }
                    break;
                default:
						return true;
						# code...
						break;
            }
        }else{
            return 'error empty';
        }
    }
}
