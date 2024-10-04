<?php

namespace App\Http\Controllers;

use App\Services\GatewayService;
use App\Services\SignatureService;
use Illuminate\Http\Request;

class GeideaGatewayController extends Controller
{
    public GatewayService $gatewayService;
   public SignatureService $signatureService;
    //
    public function __construct(
        GatewayService $gatewayService,
        SignatureService $signatureService,
    )
    {
      $this->gatewayService=$gatewayService;
      $this->signatureService=$signatureService;
    }

    /**
     * @throws \Exception
     */
    public function payOrder(Request $request): string
    {
       $request->validate([
           'amount' =>'required|numeric',
           'currency'=>'required|in:SAR,EGP,AED,QAR,OMR,BHD,KWD,USD,GBP,EUR',
        ]);
        $data=[
        'amount'=>$request->input('amount'),
        'currency'=>$request->input('currency'),
        'timestamp'=>date('Y/m/d H:i:s'),
        'merchantReferenceId'=>uniqid(),
        "paymentOperation"=>"Pay",
    ];
        return $this->gatewayService->sendRequest($data);
    }


    /**
     * @throws \Exception
     */
    public function initiateAuthentication(Request $request)
    {
        $request->validate([
            'amount' =>'required|numeric',
            'currency'=>'required|in:SAR,EGP,AED,QAR,OMR,BHD,KWD,USD,GBP,EUR',
            'sessionId'=>'required',
            'cardNumber'=>'required',
        ]);
         $data=[
         'amount'=>$request->input('amount'),
         'currency'=>$request->input('currency'),
         'timestamp'=>date('Y/m/d H:i:s'),
         "paymentOperation"=>"Pay",
         'sessionId'=>$request->input('sessionId'),
         'cardNumber'=>$request->input('cardNumber'),
     ];

         return $this->gatewayService->sendRequest($data);

    }

}
