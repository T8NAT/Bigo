<?php

namespace App\Services;

use App\Http\Requests\PaymentRequest;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GatewayService
{


    private mixed $base_url;
    private array $header;
    private mixed $api_password;
    private mixed $api_key;
    private SignatureService $signatureService;

    protected $data;

    public function __construct(SignatureService $signatureService)
    {
        $this->signatureService = $signatureService;
        $this->base_url = config('gateway.base_url');
        $this->api_key = config('gateway.api_key');
        $this->api_password = config('gateway.api_password');
        $this->header = [
            'accept' => 'application/json',
            "Content-Type" => "application/json",
        ];
    }

    /**
     * @throws \Exception
     */


    /**
     * @throws Exception
     *
     * send payment request
     */
    public function sendRequest(PaymentRequest $request)
    {
        $this->data = $request->validated();
        $this->data['timestamp'] = date('Y/m/d H:i:s');
        $this->data['merchantReferenceId'] = uniqid();
        $this->data["paymentOperation"] = "Pay";
        $this->data['callbackUrl'] = config('gateway.callback_url');
        $this->data['ReturnUrl'] = config('gateway.return_url');

        //step[1] get session id
        $create_session = $this->createSession($this->data);
        if ($create_session['success'] && $create_session['status'] == 200) {
            $this->data['sessionId'] = $create_session['sessionId'];

        }
        //step [2]  get threeDSecureId & order id
        $initiate_auth= $this->initiateAuth($this->data);
        if($initiate_auth['success'] && $initiate_auth['status']==200) {
            $this->data['orderId'] = $initiate_auth['orderId'];
            $this->data['threeDSecureId'] = $initiate_auth['threeDSecureId'];
        }

        //step [3]  send order data
        $order=$this->orderData($request);
        $autheicate_payer=$this->authenticatePayer($order);
       if($autheicate_payer['success'] && $initiate_auth['status']==200) {

            return $autheicate_payer;
        }else{

           return $autheicate_payer;
       }



       // return response()->json(['error'=>'error while integration']);
    }


    //first step [1]  create session
    public function createSession($data): array
    {
        $url = 'payment-intent/api/v2/direct/session';
        try {

            $data['signature'] = $this->signatureService->getSignature($data, $this->api_key, $this->api_password);
            $response = $this->buildRequest("POST", $url, $data);
            $response_data = $response->getData(true);
            if ($response_data['success'] && $response_data['data']['responseCode'] == "000") {
                return [
                    'success' => true,
                    'status' => $response->status(),
                    'sessionId' => $response_data['data']['session']['id'],
                ];
            }
            return [
                'success' => false,
                'status' => $response->status(),
                'error' => $response_data['data']
            ];
        } catch (Exception $exception) {
            return [
                'success' => false,
                'status' => 500,
                'error' => $exception->getMessage(),
            ];
        }
    }


    //second step [2] initiate auth

    public function initiateAuth($data): array
    {
        $url='pgw/api/v6/direct/authenticate/initiate';
        try {
              $response = $this->buildRequest('POST', $url, $data);
              $response_data = $response->getData(true);
            if($response_data['success'] && $response_data['data']['responseCode']=="000") {
                return [
                    'success' => true,
                    'status' => $response->status(),
                    'threeDSecureId'=>$response_data['data']['threeDSecureId'],
                    'orderId'=>$response_data['data']['orderId'],
                ];
            }
            return [
                'success' => false,
                'status' => $response->status(),
                'error' => $response_data['data']
            ];
        }catch (Exception $exception){
            return [
                'success'=>false,
                'status'=>500,
                'error'=>$exception->getMessage(),
            ];
        }
    }



    //step three [3]
    public function authenticatePayer($data): array
    {
        $url='pgw/api/v6/direct/authenticate/payer';
        try {
            $response = $this->buildRequest('POST', $url, $data);
            $response_data = $response->getData(true);
            if($response_data['success'] && $response_data['data']['responseCode']=="000") {
                return [
                    'success' => true,
                    'status' => $response->status(),
                      'data'=>$this->$response_data['data'],
                ];
            }
            return [
                'success' => false,
                'status' => $response->status(),
                'error' => $response_data['data']
            ];
        }catch (Exception $exception){
            return [
                'success'=>false,
                'status'=>500,
                'error'=>$exception->getMessage(),
            ];
        }
    }

    //build client request to call gateway
    protected function buildRequest($method, $url, $data): \Illuminate\Http\JsonResponse
    {
        try {
            $response = Http::withBasicAuth($this->api_key, $this->api_password)
                ->send($method, $this->base_url . $url,
                    [
                        'json' => $data,
                        'headers' => $this->header
                    ]
                );
            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'status' => $response->status(),
                    'data' => $response->json(),
                ], $response->status());
            }
            return response()->json([
                'success' => false,
                'status' => $response->status(),
                'data' => $response->json(),
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function orderData($request):array
    {
      return[
          'sessionId'  =>$this->data['sessionId'],
          'orderId'=>$this->data['orderId'],
           "paymentMethod"=>[
                        "cardholderName" => $this->data['cardholderName'],
                        "cardNumber" => $this->data['cardNumber'],
                        "cvv" => $this->data['cvv'],
                        "expiryDate" =>$request->expiryDate,
          ],
            "deviceIdentification"=> [
            "providerDeviceId"=>request()->ip(),
           "language"=>'en',
              "userAgent"=> request()->header('User-Agent'),
           ],
      ];

    }
}
