<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'charge_id' => '123e4567-e89b-12d3-a456-426655440000',
            'card_id' => 1,
            'payment_id' => 1,
            'payment_gateway' => 'IDPAY'
        ];
    }
}
