<?php

namespace App\Services;
class SignatureService
{


    function generateGatewaySignature($PublicKey, $orderAmount, $orderCurrency, $orderMerchantReferenceId, $apiPassword, $timestamp): string
    {
        $amountStr = number_format($orderAmount, 2, '.', '');
        $data = "{$PublicKey}{$amountStr}{$orderCurrency}{$orderMerchantReferenceId}{$timestamp}";
        $hash = hash_hmac('sha256', $data, $apiPassword, true);
        return base64_encode($hash);
    }


    public function getSignature($data,$api_key,$api_password): mixed
    {
        return $this->generateGatewaySignature(
            $api_key,
            $data['amount'],
            $data['currency'],
            $data['merchantReferenceId'],
            $api_password,
            $data['timestamp'],
        );
    }
}
