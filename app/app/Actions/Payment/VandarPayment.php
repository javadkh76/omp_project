<?php

namespace App\Actions\Payment;

use App\Exceptions\PaymentEndPointException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VandarPayment implements Payment
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
                'Content-Type' => 'application/json'
            ])->retry(2, 100)->post('https://ipg.vandar.io/api/v3/send', [
                'api_key' => env('VANDAR_API_KEY'),
                'factorNumber' => $chargeId,
                'amount' => $amount,
                'valid_card_number' => [$cardNumber],
                'mobile_number' => $mobile,
                'description' => $description,
                'callback_url' => $callbackUrl
            ]);
            if ($response->status() === 200) {
                $body = $response->json();
                return ['payment_id' => $body['token'], 'url' => 'https://ipg.vandar.io/v3/'.$body['token']];
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
