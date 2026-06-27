<?php

namespace Modules\Payment\Services;

use Modules\Order\Exceptions\OrderNotPayableException;
use Modules\Payment\Exceptions\PaymentFailedException;
use Modules\Payment\Models\Payment;
use Modules\Payment\Repositories\PaymentRepository;
use Modules\Payment\Gateways\PaymentGatewayInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(
        protected PaymentRepository $paymentRepository,
        protected PaymentGatewayInterface $gateway
    ) {}

    public function requestPayment($userId, $orderId)
    {
        $order = $this->paymentRepository->findPendingOrderForUser($userId, $orderId);

        if (!$order->isPending()) {
            Log::warning("تلاش برای پرداخت سفارش غیرمعتبر: Order ID {$orderId} توسط User ID {$userId}");
            throw new OrderNotPayableException('این سفارش قبلاً پرداخت شده یا وضعیت آن معتبر نیست.');
        }

        $existingPendingPayment = Payment::query()
            ->where('order_id', $order->id)
            ->where('status', 'pending')
            ->exists();

        if ($existingPendingPayment) {
            throw new OrderNotPayableException('یک درخواست پرداخت در انتظار برای این سفارش وجود دارد.');
        }

        return DB::transaction(function () use ($userId, $order) {
            $gatewayResponse = $this->gateway->requestPayment(
                amount: (float) $order->total_price,
                metadata: ['order_id' => $order->id, 'user_id' => $userId]
            );

            if (!$gatewayResponse->isSuccessful()) {
                throw new PaymentFailedException($gatewayResponse->getMessage());
            }

            $payment = $this->paymentRepository->create([
                'order_id' => $order->id,
                'amount' => $order->total_price,
                'status' => 'pending',
                'reference_id' => $gatewayResponse->getTransactionId(),
            ]);

            Log::info('درخواست پرداخت ایجاد شد', [
                'payment_id' => $payment->id,
                'order_id' => $order->id,
                'gateway' => $this->gateway->name(),
                'transaction_id' => $gatewayResponse->getTransactionId(),
            ]);

            return [
                'payment_id' => $payment->id,
                'amount' => (float) $payment->amount,
                'payment_url' => $gatewayResponse->getRedirectUrl(),
                'transaction_id' => $gatewayResponse->getTransactionId(),
                'gateway' => $this->gateway->name(),
                'message' => $gatewayResponse->getMessage(),
            ];
        });
    }

    public function verifyPayment($userId, $paymentId)
    {
        return DB::transaction(function () use ($userId, $paymentId) {
            $payment = $this->paymentRepository->findForUserWithLock($userId, $paymentId);

            if ($payment->isSuccessful()) {
                Log::notice("تلاش مجدد برای تایید پرداخت موفق: Payment ID {$paymentId}");
                throw new PaymentFailedException('این پرداخت قبلاً تایید شده است.');
            }

            if (!$payment->order->isPending()) {
                throw new OrderNotPayableException('وضعیت سفارش برای تایید پرداخت معتبر نیست.');
            }

            $gatewayResponse = $this->gateway->verifyPayment(
                paymentId: $payment->reference_id ?: (string) $payment->id
            );

            if (!$gatewayResponse->isSuccessful()) {
                $this->paymentRepository->update($payment, ['status' => 'failed']);
                throw new PaymentFailedException($gatewayResponse->getMessage());
            }

            $referenceId = $gatewayResponse->getTransactionId();

            $this->paymentRepository->update($payment, [
                'status' => 'success',
                'reference_id' => $referenceId,
            ]);

            $payment->order->update(['status' => 'paid']);

            Log::info('پرداخت با موفقیت تایید شد', [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'reference_id' => $referenceId,
                'gateway' => $this->gateway->name(),
            ]);

            return $payment->fresh(['order']);
        });
    }
}
