<?php

namespace App\Services;

class SignatureService
{

    public static function generateSignature($params, $secret): string
    {

        ksort($params);
        $queryString = http_build_query($params);
        return hash_hmac('sha256', $queryString, $secret);
    }

}
