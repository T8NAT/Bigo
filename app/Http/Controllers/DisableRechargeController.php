<?php

namespace App\Http\Controllers;

use App\Services\SignatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
class DisableRechargeController extends Controller
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
    public function disable(Request $request)
    {
        $validated = $request->validate([
            'seqid' => 'required|uuid',
        ]);
        $params = [
            'seqid' =>$validated['seqid'],
        ];
        try {
            $signature = SignatureService::generateSignature($params, $this->secret_key);
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'bigo-oauth-signature' => $signature,
                'bigo-client-id' => $this->client_id,
                'bigo-timestamp' => now()->timestamp,
            ])->post("{$this->host_domain}/sign/agent/disable", $params);

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
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
