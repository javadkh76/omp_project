<?php

namespace App\Actions\Payment;

use App\Exceptions\PaymentEndPointException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IDPay implements Payment
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
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-API-KEY' => env('IDPAY_API_KEY'),
                'X-SANDBOX' => env('PAYMENT_SANDBOX', false)
            ])->retry(2, 100)->post('https://api.idpay.ir/v1.1/payment', [
                'order_id' => $chargeId,
                'amount' => $amount,
                'name' => $name,
                'phone' => $mobile,
                'desc' => $description,
                'callback' => $callbackUrl
            ]);
            if ($response->status() === 201) {
                $body = $response->json();
                return ['payment_id' => $body['id'], 'url' => $body['link']];
            }
            return false;
        } catch (\Throwable $e) {
            Log::error($e);
            throw new PaymentEndPointException();
        }
    }

    public function inquiry(string $paymentId, string $chargeId): bool
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-API-KEY' => env('IDPAY_API_KEY'),
                'X-SANDBOX' => env('PAYMENT_SANDBOX', false)
            ])->retry(2, 100)->post('https://api.idpay.ir/v1.1/payment/inquiry', [
                'id' => $paymentId,
                'order_id' => $chargeId
            ]);
            if ($response->status() === 200) {
                $body = $response->json();
                if ($body['status'] === '10') {
                    $this->refNumber = $body['payment']['track_id'];
                    $this->trackingCode = $body['track_id'];
                    return true;
                }
            }
            return false;
        } catch (\Throwable $e) {
            Log::error($e);
            throw new PaymentEndPointException();
        }
    }

    public function verify(string $paymentId, string $chargeId): bool
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-API-KEY' => env('IDPAY_API_KEY'),
                'X-SANDBOX' => env('PAYMENT_SANDBOX', false)
            ])->retry(2, 100)->post('https://api.idpay.ir/v1.1/payment/verify', [
                'id' => $paymentId,
                'order_id' => $chargeId
            ]);
            if ($response->status() === 200) {
                $body = $response->json();
                if ($body['status'] < 100) {
                    return false;
                }
                $this->wage = $body['amount'] - $body['payment']['amount'];
                return true;
            }
            return false;
        } catch (\Throwable $e) {
            Log::error($e);
            throw new PaymentEndPointException();
        }
    }
}
