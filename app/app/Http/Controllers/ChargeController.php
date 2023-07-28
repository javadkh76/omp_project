<?php

namespace App\Http\Controllers;

use App\Actions\CreateChargeActions;
use App\Actions\Payment\IDPay;
use App\Actions\Payment\Jibit;
use App\Actions\Payment\VandarPayment;
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
    }

    public function showCharges(Request $request): JsonResponse
    {
    }

    public function removePendingCharge(Request $request, Charge $charge): JsonResponse
    {
    }
}
