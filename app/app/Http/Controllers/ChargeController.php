<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\CreateChargeRequest;
use App\Models\Charge;

class ChargeController extends Controller
{
    public function createCharge(CreateChargeRequest $request): JsonResponse
    {
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
