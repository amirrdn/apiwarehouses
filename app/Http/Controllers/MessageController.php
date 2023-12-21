<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\SmsapiService;

class MessageController extends Controller
{
    public function sendsmsMessage(Request $request, SmsapiService $sms)
    {
        $smsapi                 = $sms->sendSMS($request);
        return $smsapi;
    }
}
