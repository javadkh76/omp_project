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
        try {
            $response = Http::withToken(env('JIBIT_API_KEY'))
                ->withHeaders([
                    'Content-Type' => 'application/json'
                ])->retry(2,
                    100)->get("https://napi.jibit.ir/ppg/v3/purchases?purchaseId=$paymentId&clientReferenceNumber=$chargeId");
            if ($response->status() === 200) {
                $body = $response->json();
                if (count($body['elements']) && $body['elements'][0]['state'] === 'READY_TO_VERIFY') {
                    $this->refNumber = $body['elements'][0]['pspReferenceNumber'];
                    $this->trackingCode = $body['elements'][0]['pspTraceNumber'];
                    $this->wage = $body['elements'][0]['fee'];
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
            $response = Http::withToken(env('JIBIT_API_KEY'))
                ->withHeaders([
                    'Content-Type' => 'application/json'
                ])->retry(2, 100)->post("https://napi.jibit.ir/ppg/v3/purchases/$paymentId/verify",);
            if ($response->status() === 200) {
                $body = $response->json();
                if ($body['status'] !== 'SUCCESSFUL') {
                    return false;
                }
                return true;
            }
            return false;
        } catch (\Throwable $e) {
            Log::error($e);
            throw new PaymentEndPointException();
        }
    }
}
