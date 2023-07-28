<?php

namespace App\Http\Controllers;

use App\Actions\CreateChargeActions;
use App\Actions\Payment\IDPay;
use App\Actions\Payment\Jibit;
use App\Actions\Payment\VandarPayment;
use App\Actions\VerifyChargeActions;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\CreateChargeRequest;
use App\Models\Charge;

class ChargeController extends Controller
{
    protected array $paymentGateways = [
        'IDPAY' => IDPay::class,
        'VANDAR' => VandarPayment::class,
        'JIBIT' => Jibit::class
    ];

    public function createCharge(CreateChargeRequest $request): JsonResponse
    {
        $user = $request->user();
        $userId = $user->id;
        $card = Card::where('id', $request->card_id)
            ->where('user_id', $userId)
            ->where('status', 1)
            ->first();

        if (!$card) {
            return response()->json(['message' => 'Intended card is not exists for you'], 404);
        }

        $charge = CreateChargeActions::upsertCharge($request->amount, $userId);

        $result = CreateChargeActions::preparePaymentGateway($this->paymentGateways,
            $request->amount, $charge->id, $user);
        $payment = $result[0];
        $paymentGateway = $result[1];

        if (!$payment) {
            return response()->json(['message' => 'Cannot request to payment gateway'], 500);
        }

        CreateChargeActions::createTransaction($charge->id, $card->id, $payment['payment_id'], $paymentGateway);

        return response()->json(['message' => 'success', 'url' => $payment['url']]);
    }

    public function verify(string $id): JsonResponse
    {
        $charge = Charge::where('status', 3)->where('id', $id)->first();
        if (!$charge) {
            return response()->json(['message' => 'Charge request not found'], 404);
        }

        $transaction = VerifyChargeActions::findValidTransaction($charge);
        if (!$transaction) {
            return response()->json(['message' => 'Transaction is expired'], 404);
        }

        $paymentGateway = VerifyChargeActions::inquiryPayment($this->paymentGateways, $transaction, $charge->id);

        $verifyResult = $paymentGateway->verify($transaction->payment_id, $charge->id);
        if (!$verifyResult) {
            return response()->json(['message' => 'Transaction is failed'], 500);
        }
        VerifyChargeActions::commitChargePayment($charge, $transaction->id, $paymentGateway);

        return response()->json(['message' => 'success']);
    }

    public function showCharges(Request $request): JsonResponse
    {
        $charges = Charge::where('user_id', $request->user()->id)
            ->whereIn('status', [1, 3])
            ->paginate(10);
        return response()->json(['message' => 'success', 'charges' => $charges], 200);
    }

    public function removePendingCharge(Request $request, Charge $charge): JsonResponse
    {
        if ($charge->user_id !== $request->user()->id || $charge->status === 0) {
            return response()->json(['message' => 'Intended charge is not exists for you'], 404);
        }
        if ($charge->status === 1) {
            return response()->json(['message' => 'You cannot remove purchased charge'], 403);
        }

        $charge->update(['status' => 0]);
        return response()->json(['message' => 'success']);
    }
}
