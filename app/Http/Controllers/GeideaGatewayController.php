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


    public function subscribePayment()
    {
//     $data=[
//         'amount'=>1850,
//         'currency'=>'SAR',
//         'timestamp'=>date('Y/m/d H:i:s'),
//         'merchantReferenceId'=>'6700051424c01',
//         "paymentOperation"=>"Pay",
//     ];
//
//     return $this->gatewayService->getPaymentStatus($data);

//        "paymentMethod"=> [
//        "cardNumber"=> 5123450000000008,
//        "cardholderName"=>'Mastercar',
//        "cvv"=> 100,
//        "expiryDate"=>[
//            "month"=>01,
//            "year"=> 39,
//        ],
  //  ]
    }

}
