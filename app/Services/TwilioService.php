<?php

namespace App\Services;
use App\Models\TwilioModel;
/**
 * Class TwilioService.
 */
use Illuminate\Http\Request;
use App\Models\Mtwilio;
use Twilio\Rest\Client;

class TwilioService
{
    public function sendSMS(Request $request)
    {
        
        
        $dbtwilio                   = TwilioModel::where('store_id', \Auth::user()->store_id)->select('*')->get();
        if(count($dbtwilio) > 0){
            foreach($dbtwilio as $b){
                $account_sid        = $b->account_sid;
                $auth_token         = $b->auth_token;
                $twilio_phone       = $b->twilio_phone;

                if(empty($account_sid) || empty($auth_token) || empty($twilio_phone)){
                    return "Invalid Twilio API Details!";
                }
                try {
                    $client = new Client($account_sid, $auth_token);
                    $response   = $client->messages->create(''.$request->mobile.'', 
                    ['from' => ''.$twilio_phone, 'body' => $request->message] );
                   
                    if($response->status=='queued' || $response->status=='sent'){
                        return response()->json([
                            'message'   => 'success sent',
                            'code'  => 200
                        ]);
                    }
                    else{
                        return response()->json([
                            'message'   => 'failed',
                            'code'  => 400
                        ]);
                    }
                } catch (TwilioException $e) {
                    $db = Log::error(
                        'Could not send SMS notification.' .
                        ' Twilio replied with: ' . $e
                    );
                    return $db;
                }
            }
        }
    }
}
