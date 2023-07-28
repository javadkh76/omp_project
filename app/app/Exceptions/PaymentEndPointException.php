<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class PaymentEndPointException extends Exception
{
    public function render(): JsonResponse
    {
        $status = 502;
        $error = "Cannot request to payment endpoint";

        return response()->json(["error" => $error], $status);
    }
}
