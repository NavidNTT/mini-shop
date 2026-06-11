<?php

namespace Modules\Payment\Services;

use Modules\Payment\Repositories\PaymentRepository;
use Modules\Order\Models\Order;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentService
{
    protected $paymentRepository;

    public function __construct(PaymentRepository $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    public function requestPayment($userId, $orderId)
    {
        $order = Order::where('user_id', $userId)->findOrFail($orderId);

        if ($order->status !== 'pending') {
            Log::warning("تلاش برای پرداخت سفارش غیرمعتبر: Order ID {$orderId} توسط User ID {$userId}");
            throw new Exception('این سفارش قبلاً پرداخت شده یا وضعیت آن معتبر نیست.');
        }

        try {
            $payment = $this->paymentRepository->create([
                'order_id' => $order->id,
                'amount' => $order->total_price,
                'status' => 'pending'
            ]);

            Log::info("درخواست پرداخت ایجاد شد: Payment ID {$payment->id} برای Order ID {$order->id}");

            return [
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'payment_url' => url("/api/payment/verify?payment_id={$payment->id}"), // آدرس فرضی برای هدایت به درگاه
                'message' => 'درخواست پرداخت با موفقیت ایجاد شد.'
            ];
        } catch (Exception $e) {
            Log::error("خطا در ایجاد درخواست پرداخت: " . $e->getMessage());
            throw $e;
        }
    }

    public function verifyPayment($userId, $paymentId)
    {
        $payment = $this->paymentRepository->findById($paymentId);
        
        if (!$payment) {
            Log::error("تلاش برای تایید پرداخت ناموجود: Payment ID {$paymentId}");
            throw new Exception('اطلاعات پرداخت یافت نشد.');
        }

        $order = Order::where('user_id', $userId)->findOrFail($payment->order_id);

        if ($payment->status === 'success') {
            Log::notice("تلاش مجدد برای تایید پرداخت موفق: Payment ID {$paymentId}");
            throw new Exception('این پرداخت قبلاً تایید شده است.');
        }

        return DB::transaction(function () use ($payment, $order) {
            try {
                // شبیه‌سازی تایید پرداخت از سمت بانک
                $referenceId = Str::random(12); 

                $this->paymentRepository->update($payment, [
                    'status' => 'success', 
                    'reference_id' => $referenceId
                ]);

                $order->update(['status' => 'paid']);

                Log::info("پرداخت با موفقیت تایید شد: Payment ID {$payment->id}, Ref ID: {$referenceId}");

                return $payment;
            } catch (Exception $e) {
                Log::error("خطا در تایید تراکنش مالی: " . $e->getMessage());
                throw new Exception('خطا در پردازش نهایی پرداخت.');
            }
        });
    }
}