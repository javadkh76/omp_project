<?php

namespace App\Actions\Payment;

use App\Exceptions\PaymentEndPointException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Jibit implements Payment
{
    use PaymentTrait;

    public static function create(
        int $amount,
        string $chargeId,
        string $callbackUrl,
        ?string $description = "",
        ?string $name = "",
        ?string $mobile = "",
        ?int $cardNumber = 0
    ): array|bool {
        try {
            $response = Http::withToken(env('JIBIT_API_KEY'))
                ->withHeaders([
                    'Content-Type' => 'application/json'
                ])->retry(2, 100)->post('https://napi.jibit.ir/ppg/v3/purchases', [
                    'clientReferenceNumber' => $chargeId,
                    'amount' => $amount,
                    'currency' => 'IRR',
                    'payerCardNumber' => $cardNumber,
                    'payerMobileNumber' => $mobile,
                    'description' => $description,
                    'callbackUrl' => $callbackUrl
                ]);
            // not sure about success status code
            if ($response->status() === 200) {
                $body = $response->json();
                return ['payment_id' => $body['purchaseId'], 'url' => $body['pspSwitchingUrl']];
            }
            return false;
        } catch (\Throwable $e) {
            Log::error($e);
            throw new PaymentEndPointException();
        }
    }

    public function inquiry(string $paymentId, string $chargeId): bool
    {
    }

    public function verify(string $paymentId, string $chargeId): bool
    {
    }
}
