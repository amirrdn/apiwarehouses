<?php

namespace App\Services;
use App\Models\SmsapiModel;
use App\Services\TwilioService;
use Illuminate\Http\Request;
/**
 * Class SmsapiService.
 */
class SmsapiService
{
    public function sendSMS(Request $request)
    {
        $store_id                   = \Auth::user()->store_id;
        $dbstore                    = \DB::table('db_store')
                                    ->where('id', $store_id)
                                    ->first();
        $sms_status                 = $dbstore->sms_status;

        if($sms_status == 0){
            return response()->json([
                'message'   => "Sorry! Can't Send.Please Enable SMS",
                'code'  => 400
            ]);
        }
        $dbsms                      = SmsapiModel::where('store_id', $store_id)->select('*')->get();
        // return $dbsms;
        if(count($dbsms) > 0){
            $api    = array();
            if($sms_status == 1){

                foreach($dbsms as $b){
                    if($b->info == 'message'){
                        $api = array_merge($api, [$b->key => ($request->message)]);
                    } else if($b->info == 'mobile'){
                        $api = array_merge($api, [$b->key => $request->mobile]);
                    } else {
                        $api = array_merge($api, [$b->key => $b->key_value]);
                    }
                }
                $api = array_merge($api, ['unicode' => '1']);
                $ch = curl_init();
                $data = http_build_query($api);
                $getUrl = $api['weblink']."?".$data;
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_URL, $getUrl);
                curl_setopt($ch, CURLOPT_TIMEOUT, 80);
    
                $response = curl_exec($ch);
    
                if(curl_error($ch)){
                    return 'failed';
                } else {
                    return 'success';
                }
                curl_close($ch);
            }

            if($sms_status == 2){
                $request->merge([
                    'mobile'    => $request->mobile,
                    'message'   => $request->message
                ]);
                return (new TwilioService())->sendSMS($request);
            }
        }
    }
}
