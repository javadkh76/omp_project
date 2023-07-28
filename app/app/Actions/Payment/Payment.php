<?php

namespace App\Actions\Payment;

interface Payment
{
    public static function create(
        int $amount,
        string $chargeId,
        string $callbackUrl,
        ?string $description = "",
        ?string $name = "",
        ?string $mobile = "",
        ?int $cardNumber = 0
    ): array|bool;

    public function inquiry(string $paymentId, string $chargeId): bool;

    public function verify(string $paymentId, string $chargeId): bool;

    public function getWage(): int;

    public function getRefNumber(): string;

    public function getTrackingCode(): string;
}
