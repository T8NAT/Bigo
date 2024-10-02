<?php
namespace App\Traits;

trait HandlesApiErrors
{
    protected $errorCodes = [
        7212001 => ['message' => 'The recharge API was disabled by Bigo.', 'status' => 400],
        7212004 => ['message' => 'The recharge_bigoid does not exist.', 'status' => 404],
        7212010 => ['message' => 'The orderid is duplicated.', 'status' => 409],
        7212011 => ['message' => 'Insufficient balance in the reseller account.', 'status' => 402],
        7212012 => ['message' => 'Too many requests. Please wait a moment and try again.', 'status' => 429],
        7212008 => ['message' => 'The number of diamonds exceeds the allowed limit.', 'status' => 400],
        500001  => ['message' => 'Other errors, contact Bigo team.', 'status' => 500],
    ];

    /**
     * معالجة كود الاستجابة من API
     *
     * @param int $rescode
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleApiError($rescode,$response=null)
    {
        if ($rescode === 0) {
            return response()->json([
                'status' => 'success',
                'message' => 'Operation completed successfully.',
            ], 200);
        }
        if (isset($this->errorCodes[$rescode])) {
            $error = $this->errorCodes[$rescode];
            return response()->json([
                'status' => 'error',
                'message' => $error['message'],
            ], $error['status']);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'An unknown error occurred. Please contact support.',
        ], 500);
    }
}

