<?php

declare(strict_types=1);

namespace LeviLabs\LaravelFastpay;

use Illuminate\Support\Facades\Event;
use LeviLabs\LaravelFastpay\Console\Commands\SyncFastpayStatuses;
use LeviLabs\LaravelFastpay\Contracts\FastpayPaymentServiceContract;
use LeviLabs\LaravelFastpay\Contracts\FastpayQrServiceContract;
use LeviLabs\LaravelFastpay\Events\PaymentInitiated;
use LeviLabs\LaravelFastpay\Events\PaymentRefunded;
use LeviLabs\LaravelFastpay\Events\PaymentValidated;
use LeviLabs\LaravelFastpay\Listeners\PersistFastpayPayment;
use LeviLabs\LaravelFastpay\Services\FastpayPaymentService;
use LeviLabs\LaravelFastpay\Services\FastpayQrService;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FastpayServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('fastpay')
            ->hasConfigFile('fastpay')
            ->hasMigrations('create_fastpay_payments_table', 'create_fastpay_refunds_table')
            ->runsMigrations()
            ->hasCommand(SyncFastpayStatuses::class);
    }

    public function registeringPackage(): void
    {
        $this->app->singleton(FastpayPaymentServiceContract::class, FastpayPaymentService::class);
        $this->app->singleton(FastpayQrServiceContract::class, FastpayQrService::class);
    }

    public function packageBooted(): void
    {
        Event::listen(PaymentInitiated::class, [PersistFastpayPayment::class, 'onInitiated']);
        Event::listen(PaymentValidated::class, [PersistFastpayPayment::class, 'onValidated']);
        Event::listen(PaymentRefunded::class, [PersistFastpayPayment::class, 'onRefunded']);
    }
}
