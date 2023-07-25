<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCardRequest;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CardController extends Controller
{
    public function showCards(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $cards = Card::where('user_id', $userId)->get();
        return response()->json(['message' => 'success', 'list' => $cards]);
    }

    public function addCard(AddCardRequest $request): JsonResponse
    {
        $userId = $request->user()->id;
        Card::create([...$request->all(), 'user_id' => $userId]);
        return response()->json(['message' => 'success']);
    }

    public function removeCard(Request $request, int $card): JsonResponse
    {
        $card = Card::find($card);
        if (!$card) {
            return response()->json(['message' => 'Intended card is not exists'], 404);
        }
        if ($card->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Intended card is not exists for you'], 404);
        }
        $card->delete();
        return response()->json(['message' => 'success']);
    }
}
