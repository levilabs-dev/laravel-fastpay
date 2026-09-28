<?php

declare(strict_types=1);

namespace LeviLabs\LaravelFastpay\Facades;

use Illuminate\Support\Facades\Facade;
use LeviLabs\LaravelFastpay\Contracts\FastpayPaymentServiceContract;
use LeviLabs\LaravelFastpay\Data\CartItem;
use LeviLabs\LaravelFastpay\Data\PaymentInitiationData;
use LeviLabs\LaravelFastpay\Data\PaymentValidationData;
use LeviLabs\LaravelFastpay\Data\RefundData;
use LeviLabs\LaravelFastpay\Data\RefundValidationData;

/**
 * @method static PaymentInitiationData initiate(string $orderId, array<int, CartItem|array<string, mixed>> $cart, ?float $amount = null, ?string $successUrl = null, ?string $cancelUrl = null, ?string $callbackUrl = null, ?string $store = null)
 * @method static PaymentValidationData validate(string $orderId, ?string $store = null)
 * @method static RefundData refund(string $orderId, string $msisdn, float $amount, ?string $store = null)
 * @method static RefundValidationData refundStatus(string $orderId, ?string $store = null)
 *
 * @see FastpayPaymentServiceContract
 */
class FastpayPayment extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FastpayPaymentServiceContract::class;
    }
}
