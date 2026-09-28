<?php

declare(strict_types=1);

namespace LeviLabs\LaravelFastpay\Facades;

use Illuminate\Support\Facades\Facade;
use LeviLabs\LaravelFastpay\Contracts\FastpayQrServiceContract;
use LeviLabs\LaravelFastpay\Data\PaymentValidationData;
use LeviLabs\LaravelFastpay\Data\QrData;
use LeviLabs\LaravelFastpay\Data\QrStatusData;
use LeviLabs\LaravelFastpay\Data\RefundData;

/**
 * @method static QrData generate(string $orderId, float $amount, ?string $store = null)
 * @method static PaymentValidationData validate(string $orderId, ?string $store = null)
 * @method static QrStatusData status(string $orderId, ?string $store = null, bool $confirmIfPaid = false)
 * @method static RefundData refund(string $orderId, string $msisdn, float $amount, ?string $store = null)
 *
 * @see FastpayQrServiceContract
 */
class FastpayQr extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FastpayQrServiceContract::class;
    }
}
