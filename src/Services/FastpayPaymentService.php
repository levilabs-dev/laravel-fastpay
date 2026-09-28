<?php

declare(strict_types=1);

namespace LeviLabs\LaravelFastpay\Services;

use LeviLabs\LaravelFastpay\Contracts\FastpayPaymentServiceContract;
use LeviLabs\LaravelFastpay\Data\CartItem;
use LeviLabs\LaravelFastpay\Data\PaymentInitiationData;
use LeviLabs\LaravelFastpay\Data\PaymentValidationData;
use LeviLabs\LaravelFastpay\Data\RefundData;
use LeviLabs\LaravelFastpay\Data\RefundValidationData;
use LeviLabs\LaravelFastpay\Events\PaymentInitiated;
use LeviLabs\LaravelFastpay\Events\PaymentRefunded;
use LeviLabs\LaravelFastpay\Events\PaymentValidated;
use LeviLabs\LaravelFastpay\Exceptions\FastpayException;
use LeviLabs\LaravelFastpay\Services\Concerns\TalksToFastpay;

final class FastpayPaymentService implements FastpayPaymentServiceContract
{
    use TalksToFastpay;

    /**
     * Initiates a payment and returns the redirect URI to send the customer
     * to. The bill amount defaults to the sum of the cart's sub-totals.
     *
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
    ): PaymentInitiationData {
        $this->assertValidOrderId($orderId);

        $items = CartItem::collection($cart);
        $amount ??= array_sum(array_map(fn (CartItem $item): float => $item->subTotal(), $items));

        if ($amount <= 0) {
            throw FastpayException::invalidAmount($amount);
        }

        $store ??= (string) config('fastpay.default');
        $currency = (string) config('fastpay.currency', 'IQD');
        $config = $this->storeConfig($store);

        $data = $this->post($store, 'pgw', 'payment initiation', '/api/v1/public/pgw/payment/initiation', array_filter([
            'store_id' => $config['store_id'],
            'store_password' => $config['store_password'],
            'order_id' => $orderId,
            'bill_amount' => (int) round($amount),
            'currency' => $currency,
            'success_url' => $successUrl ?? config('fastpay.success_url'),
            'cancel_url' => $cancelUrl ?? config('fastpay.cancel_url'),
            'callback_url' => $callbackUrl ?? config('fastpay.callback_url'),
            'cart' => json_encode(array_map(fn (CartItem $item): array => $item->toArray(), $items)),
        ], fn ($value) => $value !== null));

        $initiation = PaymentInitiationData::fromArray($data);

        PaymentInitiated::dispatch($initiation, $orderId, $amount, $currency, $store);

        return $initiation;
    }

    /**
     * Fetches the authoritative payment result directly from FastPay.
     *
     * Always call this before fulfilling an order — the IPN webhook body is
     * not signed, so anyone who learns your callback URL could POST a fake
     * "Success" payload. This is FastPay's own mandated security step.
     */
    public function validate(string $orderId, ?string $store = null): PaymentValidationData
    {
        $this->assertValidOrderId($orderId);

        $store ??= (string) config('fastpay.default');
        $config = $this->storeConfig($store);

        $data = $this->post($store, 'pgw', 'payment validation', '/api/v1/public/pgw/payment/validate', [
            'store_id' => $config['store_id'],
            'store_password' => $config['store_password'],
            'order_id' => $orderId,
        ]);

        $validation = PaymentValidationData::fromArray($data);

        PaymentValidated::dispatch($validation, $store);

        return $validation;
    }

    /**
     * Refunds a paid order to the customer's FastPay wallet. Requires the
     * refund_secret_key from the Merchant Panel. This moves real money —
     * gate calls behind your own approval workflow.
     */
    public function refund(string $orderId, string $msisdn, float $amount, ?string $store = null): RefundData
    {
        $this->assertValidOrderId($orderId);
        $this->assertValidMsisdn($msisdn);

        if ($amount <= 0) {
            throw FastpayException::invalidAmount($amount);
        }

        $store ??= (string) config('fastpay.default');
        $config = $this->storeConfig($store);

        if ($config['refund_secret_key'] === null || $config['refund_secret_key'] === '') {
            throw FastpayException::missingRefundSecretKey($store);
        }

        $data = $this->post($store, 'pgw', 'payment refund', '/api/v1/public/pgw/payment/refund', [
            'store_id' => $config['store_id'],
            'store_password' => $config['store_password'],
            'order_id' => $orderId,
            'amount' => (int) round($amount),
            'refund_secret_key' => $config['refund_secret_key'],
            'msisdn' => $msisdn,
        ]);

        $refund = RefundData::fromArray($data);

        PaymentRefunded::dispatch($refund, $orderId, $amount, $msisdn, $store);

        return $refund;
    }

    /**
     * Checks whether an order has been refunded.
     */
    public function refundStatus(string $orderId, ?string $store = null): RefundValidationData
    {
        $this->assertValidOrderId($orderId);

        $store ??= (string) config('fastpay.default');
        $config = $this->storeConfig($store);

        $data = $this->post($store, 'pgw', 'refund validation', '/api/v1/public/pgw/payment/refund/validation', [
            'store_id' => $config['store_id'],
            'store_password' => $config['store_password'],
            'order_id' => $orderId,
        ]);

        return RefundValidationData::fromArray($data);
    }
}
