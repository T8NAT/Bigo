<?php

namespace App\Http\Controllers;

use App\Services\SignatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RechargePrecheckController extends Controller
{
    public mixed $client_id;
    public mixed $secret_key;
    public mixed $host_domain;
    public SignatureService $signatureService;
   public function __construct(SignatureService $signatureService)
   {
       $this->client_id=config('bigo.client_id');

      $this->secret_key =config('bigo.secret');

      $this->host_domain = config('bigo.host_domain');

      $this->signatureService=$signatureService;
   }

    public function precheck(Request $request)
    {
        $validated = $request->validate([
            'recharge_bigoid' => 'required|string',
        ]);
        $params = [
            'recharge_bigoid' => $validated['recharge_bigoid'],
            'reseller_bigoid' => $this->client_id,
            'seqid' => uniqid(),
        ];
        try {
        $signature = $this->signatureService->generateSignature('/sign/agent/recharge_pre_check',json_encode($params), now()->timestamp, $this->secret_key);
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'bigo-oauth-signature' => $signature,
                'bigo-client-id' => $this->client_id,
                'client_version'=>1,
                'bigo-timestamp' => now()->timestamp,
            ])->post("{$this->host_domain}/sign/agent/recharge_pre_check", $params);
            //success request
            if ($response->successful()) {
                return response()->json([
                    'success'=>true,
                    'status' => $response->status(),
                    'data' => $response->json(),
                ],$response->status());
                //failed request
            } else {
                return response()->json([
                    'success'=>false,
                    'status' => $response->status(),
                    'message' => $response->json(),
                ], $response->status());
            }

            //exception error
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
