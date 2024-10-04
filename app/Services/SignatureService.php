<?php

namespace App\Services;

use phpseclib3\Crypt\Hash;
use phpseclib3\Crypt\PublicKeyLoader;

class SignatureService
{

//    public static function generateSignature($params, $secret): string
//    {
//
//        ksort($params);
//        $queryString = http_build_query($params);
//        return hash_hmac('sha256', $queryString, $secret);
//    }


//    public function generateSignature($uri, $request, $timestamp,$privateKey): string
//    {
//        $strData = $request . $uri . $timestamp;
//        $digest = new Hash('sha256');
//        $digestHash = $digest->hash($strData);
//        $privateKey = PublicKeyLoader::loadPrivateKey(base64_decode($privateKey))
//            ->withHash('sha256');
//        $signature = $privateKey->sign($digestHash);
//
//        return base64_encode($signature);
//    }



    function generateGatewaySignature($PublicKey, $orderAmount, $orderCurrency, $orderMerchantReferenceId, $apiPassword, $timestamp): string
    {
        $amountStr = number_format($orderAmount, 2, '.', '');
        $data = "{$PublicKey}{$amountStr}{$orderCurrency}{$orderMerchantReferenceId}{$timestamp}";
        $hash = hash_hmac('sha256', $data, $apiPassword, true);
        return base64_encode($hash);
    }
}
