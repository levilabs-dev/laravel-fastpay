<?php

declare(strict_types=1);

namespace LeviLabs\LaravelFastpay\Events;

use Illuminate\Foundation\Events\Dispatchable;
use LeviLabs\LaravelFastpay\Data\PaymentValidationData;

final class PaymentValidated
{
    use Dispatchable;

    public function __construct(
        public readonly PaymentValidationData $validation,
        public readonly string $store,
    ) {}
}
