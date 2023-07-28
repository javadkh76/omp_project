<?php

namespace Tests\Feature;

use App\Http\Controllers\ChargeController;
use App\Http\Requests\CreateChargeRequest;
use App\Models\Charge;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Card;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class ChargeControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_user_can_create_charge_request(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('access token')->plainTextToken;

        $card = Card::factory()->create(['user_id' => $user->id]);

        $response = $this->postJson('/api/charges', ['card_id' => $card->id, 'amount' => 10000],
            ['Authorization' => 'Bearer '.$token]);

        $response
            ->assertStatus(200)
            ->assertSeeInOrder(['message', 'url']);
    }

    public function test_the_user_can_verify_charge_request(): void
    {
        Http::fake([
            // Stub a JSON response for GitHub endpoints...
            'https://api.idpay.ir/v1.1/payment/inquiry' => Http::response([
                'status' => '10', 'payment' => ['track_id' => 123456],
                'track_id' => 5446336
            ], 200),
            'https://api.idpay.ir/v1.1/payment/verify' => Http::response([
                'status' => 100,
                'amount' => 12000,
                'payment' => ['amount' => 11500]
            ], 200),
        ]);

        $user = User::factory()->create();
        $card = Card::factory()->create(['user_id' => $user->id]);
        $charge = Charge::factory()->create(['user_id' => $user->id]);
        $paymentId = Str::random(30);
        $transaction = Transaction::factory()->create([
            'charge_id' => $charge->id, 'card_id' => $card->id, 'payment_id' => $paymentId
        ]);


        $chargeController = new ChargeController();
        $chargeController->verify($charge->id);

        $updatedUser = User::find($user->id);
        $updatedCharge = Charge::find($charge->id);
        $updateTransaction = Transaction::find($transaction->id);

        $this->assertEquals(11500, $updatedUser->balance);
        $this->assertEquals(1, $updatedCharge->status);
        $this->assertEquals(1, $updateTransaction->status);
    }

    public function test_the_user_can_create_charge_request_when_first_payment_gateway_failed(): void
    {
        Http::fake([
            // Stub a JSON response for GitHub endpoints...
            'https://api.idpay.ir/v1.1/payment' => Http::response([
            ], 400),
            'https://ipg.vandar.io/api/v3/send' => Http::response([
                'token' => Str::random(35)
            ], 200),
        ]);

        $user = User::factory()->create();
        $card = Card::factory()->create(['user_id' => $user->id]);

        $request = new class($user, $card->id) extends CreateChargeRequest {
            public int $amount = 10000;

            public function __construct(public object $user, public int $card_id)
            {
            }

            public function user($guard = null): object
            {
                return $this->user;
            }
        };

        $chargeController = new ChargeController();
        $chargeController->createCharge($request);

        $charge = Charge::where('user_id', $user->id)->where('amount', 10000)->first();
        $transaction = $charge->transactions()->first();

        $this->assertNotNull($charge);
        $this->assertNotNull($transaction);
    }
}
