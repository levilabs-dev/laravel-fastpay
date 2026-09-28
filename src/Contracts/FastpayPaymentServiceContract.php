<?php

declare(strict_types=1);

namespace LeviLabs\LaravelFastpay\Contracts;

use LeviLabs\LaravelFastpay\Data\CartItem;
use LeviLabs\LaravelFastpay\Data\PaymentInitiationData;
use LeviLabs\LaravelFastpay\Data\PaymentValidationData;
use LeviLabs\LaravelFastpay\Data\RefundData;
use LeviLabs\LaravelFastpay\Data\RefundValidationData;

interface FastpayPaymentServiceContract
{
    /**
     * @param  array<int, CartItem|array<string, mixed>>  $cart
     */
    public function initiate(
        string $orderId,
        array $cart,
        ?float $amount = null,
        ?string $successUrl = null,
        ?string $cancelUrl = null,
        ?string $callbackUrl = null,
        ?string $store = null,
    ): PaymentInitiationData;

    public function validate(string $orderId, ?string $store = null): PaymentValidationData;

    public function refund(string $orderId, string $msisdn, float $amount, ?string $store = null): RefundData;

    public function refundStatus(string $orderId, ?string $store = null): RefundValidationData;
}
