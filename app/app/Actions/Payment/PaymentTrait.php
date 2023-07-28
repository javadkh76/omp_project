<?php

namespace App\Actions\Payment;

trait PaymentTrait
{
    protected int $wage = 0;
    protected string $refNumber = "";
    protected string $trackingCode = "";

    public function getWage(): int
    {
        return $this->wage;
    }

    public function getRefNumber(): string
    {
        return $this->refNumber;
    }

    public function getTrackingCode(): string
    {
        return $this->trackingCode;
    }
}
