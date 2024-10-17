<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Services\GatewayService;

use Illuminate\Http\Request;

class GeideaGatewayController extends Controller
{
    public GatewayService $gatewayService;

    public function __construct(  GatewayService $gatewayService)
    {
      $this->gatewayService=$gatewayService;
    }

    /**
     * @throws \Exception
     */
    public function payOrder(PaymentRequest $request)
    {
        try {
            return $this->gatewayService->sendRequest($request);
        }catch (\Exception $exception){
            return  $exception->getMessage();
        }

    }




}
