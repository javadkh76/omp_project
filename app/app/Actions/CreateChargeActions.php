<?php

namespace App\Actions;

use App\Models\Charge;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class CreateChargeActions
{
    public static function createTransaction(
        string $chargeId,
        int $cardId,
        string $paymentId,
        string $paymentGateway
    ): void {
        DB::transaction(function () use ($chargeId, $cardId, $paymentId, $paymentGateway) {
            Transaction::where('charge_id', $chargeId)
                ->where('status', 3)
                ->update(['status' => 0]);

            Transaction::create([
                'charge_id' => $chargeId,
                'card_id' => $cardId,
                'payment_id' => $paymentId,
                'payment_gateway' => $paymentGateway,
            ]);
        }, 3);
    }

    public static function preparePaymentGateway(
        array $paymentGateways,
        int $amount,
        string $chargeId,
        object $user
    ): array {
        $callbackUrl = env('APP_URL').'/api/charges/verify/'.$chargeId;
        $payment = false;

        // Iterate between payment gateways and assign one that works.
        foreach ($paymentGateways as $paymentGateway => $PaymentGatewayClass) {
            $payment = $PaymentGatewayClass::create($amount, $chargeId, $callbackUrl,
                'شارژ حساب',
                $user->name, $user->mobile);
            if ($payment) {
                break;
            }
        }
        return [$payment, $paymentGateway];
    }

    public static function upsertCharge(int $amount, int $userId)
    {
        return Charge::updateOrCreate([
            'amount' => $amount,
            'user_id' => $userId,
            'status' => 3
        ]);
    }
}
