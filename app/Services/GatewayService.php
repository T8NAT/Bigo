<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GatewayService
{
    private Http $client_request;
    private mixed $base_url;
    private array $header;
    private mixed $api_password;
    private mixed $api_key;
    private SignatureService $signatureService;

    public function __construct(Http $client_request, SignatureService $signatureService)
    {
         $this->signatureService=$signatureService;
        $this->client_request = $client_request;
        $this->base_url = config('gateway.base_url');
        $this->api_key=config('gateway.api_key');
        $this->api_password=config('gateway.api_password');
        $this->header=[
            "Content-Type: application/json",
            "Authorization: Basic " . base64_encode("$this->api_key:$this->api_password")
        ];

    }

    /**
     * @throws \Exception
     */
    protected function buildRequest($method, $url, $data): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\JsonResponse|\Illuminate\Http\Client\Response
    {
        try {
            $signature=$this->signatureService->generateGatewaySignature(
                $this->api_key,
                $data['amount'],
                $data['currency'],
                $data['merchantReferenceId'],
                $this->api_password,
                $data['timestamp'],
            );
            $data['signature']= $signature;

            $response=$this->client_request::withHeaders(
                $this->header
            )->send($method, $this->base_url.$url, ['json' => $data]);
            if($response->successful()) {
                return $response;
            }
            return response()->json(['error' => 'failed request'], 500);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }

    /**
     * @throws \Exception
     */
    public function sendPayment($data): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\JsonResponse|\Illuminate\Http\Client\Response
    {

       return $this->buildRequest('POST','payment-intent/api/v2/direct/session',$data);
    }

    /**
     * @throws \Exception
     */
    public function getPaymentStatus($data): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\JsonResponse|\Illuminate\Http\Client\Response
    {

        return  $this->buildRequest('POST', '/v2/getPaymentStatus', $data);
    }
}
