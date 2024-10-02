<?php

namespace App\Http\Controllers;

use App\Services\SignatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class DiamondRechargeController extends Controller
{
    private mixed $client_id;
    private mixed $secret_key;
    private mixed $host_domain;
    public function __construct()
    {
        $this->client_id=config('bigo.client_id');

        $this->secret_key =config('bigo.secret');

        $this->host_domain = config('bigo.host_domain');
    }

    public function recharge(Request $request)
    {
        $validated = $request->validate([
             'recharge_bigoid' => 'required|string',
              'seqid'=>'required|uuid',
              'bu_orderid' => 'required|string|max:40',
              'value' => 'required|integer',
             'total_cost' => 'required|numeric|between:0,99999999999.00',
            'currency' => ['required', Rule::in(config('currencies.supported'))],
        ]);
        //configuration data
        $params = [
            'recharge_bigoid' => $validated['recharge_bigoid'],
            'reseller_bigoid' => $this->client_id,
            'seqid' =>$validated['seqid'],
            'bu_orderid' => $validated['bu_orderid'],
            'value' => $validated['value'],
            'total_cost' => $validated['total_cost'],
            'currency'=>$validated['currency'],
        ];
        try {
            $signature = SignatureService::generateSignature($params, $this->secret_key);
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'bigo-oauth-signature' => $signature,
                'bigo-client-id' => $this->client_id,
                'bigo-timestamp' => now()->timestamp,
            ])->post("{$this->host_domain}/sign/agent/rs_recharge", $params);

            if ($response->successful()) {
                return response()->json([
                    'success'=>true,
                    'status' => $response->status(),
                    'data' => $response->json(),
                    ],$response->status());
            } else {
                return response()->json([
                    'success' => false,
                    'status'=>$response->status(),
                    'message' => $response->json(),
                ], $response->status());
            }



        } catch (\Exception $e) {
            return response()->json([
                'success'=>false,
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}
