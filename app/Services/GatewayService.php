<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GatewayService
{

    public Client $client;
    private mixed $base_url;
    private array $header;
    private mixed $api_password;
    private mixed $api_key;
    private SignatureService $signatureService;

    public function __construct(Client $client, SignatureService $signatureService)
    {
        $this->client=$client;
         $this->signatureService=$signatureService;
        $this->base_url = config('gateway.base_url');
        $this->api_key=config('gateway.api_key');
        $this->api_password=config('gateway.api_password');

        $this->header=[
            'accept' => 'application/json',
            "Content-Type" =>"application/json",
            "Authorization"=> 'Basic '. base64_encode("$this->api_key:$this->api_password")
        ];

    }

    /**
     * @throws \Exception
     */
    protected function buildRequest($method, $url, $data): string
    {
        try {
            $response=Http::withHeaders(
                $this->header
            )->post($this->base_url.$url,$data);
            if($response->successful()) {
                return response()->json([
                   'success'=>true,
                    'status' => $response->status(),
                    'data' => $response->json(),
                ],$response->status());
            }
            return response()->json([
                'success'=>false,
                'status' => $response->status(),
                'data' => $response->json(),
            ],$response->status());

        }catch (\Exception $e) {
            return response()->json([
                'success'=>false,
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @throws \Exception
     */
    public function sendRequest(Request $request)
    {
        $request->validate([
            'amount' =>'required|numeric',
            'currency'=>'required|in:SAR,EGP,AED,QAR,OMR,BHD,KWD,USD,GBP,EUR',
            'cardNumber'=>'required',
        ]);
        $data=[
            'amount'=>$request->input('amount'),
            'currency'=>$request->input('currency'),
            'timestamp'=>date('Y/m/d H:i:s'),
            'merchantReferenceId'=>uniqid(),
            "paymentOperation"=>"Pay",
        ];
        $checkout_response=$this->buildRequest('POST','payment-intent/api/v2/direct/session',$data);

        if($checkout_response['success']){

            return  $checkout_response;
        }

        $data['signature']= $this->getSignature($data);
        $data['cardNumber']=$request->input('cardNumber');
        $data[ 'callbackUrl']=config('gateway.callback_url');
        $data[ 'ReturnUrl']=config('gateway.return_url');


       return  $data;
    }


    /**
     * @return mixed
     */
    public function getSignature($data): mixed
    {
       return $this->signatureService->generateGatewaySignature(
            $this->api_key,
            $data['amount'],
            $data['currency'],
            $data['merchantReferenceId'],
            $this->api_password,
            $data['timestamp'],
        );
    }
    /**
     * @throws \Exception
     */
    public function getPaymentStatus($data): string
    {
        return  $this->buildRequest('POST', 'payment-intent/api/v2/direct/session-subscription', $data);
    }
}
