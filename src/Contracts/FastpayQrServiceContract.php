<?php

declare(strict_types=1);

namespace LeviLabs\LaravelFastpay\Contracts;

use LeviLabs\LaravelFastpay\Data\PaymentValidationData;
use LeviLabs\LaravelFastpay\Data\QrData;
use LeviLabs\LaravelFastpay\Data\QrStatusData;
use LeviLabs\LaravelFastpay\Data\RefundData;

interface FastpayQrServiceContract
{
    public function generate(string $orderId, float $amount, ?string $store = null): QrData;

    public function validate(string $orderId, ?string $store = null): PaymentValidationData;

    public function status(string $orderId, ?string $store = null, bool $confirmIfPaid = false): QrStatusData;

    public function refund(string $orderId, string $msisdn, float $amount, ?string $store = null): RefundData;
}
