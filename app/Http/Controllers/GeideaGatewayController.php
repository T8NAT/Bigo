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

        return $this->gatewayService->sendRequest($request);


}
}
