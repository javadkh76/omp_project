<?php

namespace App\Actions;

use App\Actions\Payment\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class VerifyChargeActions
{
    public static function findValidTransaction(object $charge): object
    {
        return $charge
            ->transactions
            ->where('status', 3)
            ->where('created_at', '>', date('Y-m-d H:i:s', time() - 15 * 60))
            ->first();
    }
    public static function inquiryPayment(array $paymentGateways, object $transaction, string $chargeId): Payment
    {
        $PaymentGatewayClass = $paymentGateways[$transaction->payment_gateway];
        $paymentGateway = new $PaymentGatewayClass();
        $paymentStatus = $paymentGateway->inquiry($transaction->payment_id, $chargeId);
        if (!$paymentStatus) {
            throw new \Exception('Transaction is failed');
        }
        return $paymentGateway;
    }
    public static function commitChargePayment(object $charge, $transactionId, Payment $paymentGateway): void
    {
        DB::transaction(function () use ($charge, $transactionId, $paymentGateway) {
            User::where('id', $charge->user_id)->increment('balance', $charge->amount - $paymentGateway->getWage());

            $chargeAffected = DB::table('charges')->where('id', $charge->id)->update(['status' => 1]);

            $transactionAffected = DB::table('transactions')->where('id', $transactionId)->update([
                'status' => 1,
                'wage' => $paymentGateway->getWage(),
                'ref_number' => $paymentGateway->getRefNumber(),
                'tracking_code' => $paymentGateway->getTrackingCode(),
            ]);

            if (!$chargeAffected || !$transactionAffected) {
                throw new \Exception('Transaction already verified');
            }
        }, 3);
    }
}
