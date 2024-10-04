<?php

namespace App\Http\Controllers;

use App\Services\GatewayService;
use App\Services\SignatureService;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\Array_;

class GeideaGatewayController extends Controller
{
    public GatewayService $gatewayService;
   public SignatureService $signatureService;
    //
    public array $data;
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
    public function payOrder()
    { $this->data=[
        'amount'=>'1000',
        'currency'=>'SAR',
        'timestamp'=>now()->timestamp,
        'merchantReferenceId'=>uniqid(),
        'callbackUrl'=>config('gateway.callback_url'),
        'ReturnUrl'=>config('gateway.return_url'),
    ];
       return $this->gatewayService->sendPayment($this->data);
    }

}
