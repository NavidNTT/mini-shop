<?php

namespace Modules\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Payment\Services\PaymentService;
use Modules\Payment\Http\Requests\PaymentRequestRequest;
use Modules\Payment\Http\Requests\PaymentVerifyRequest;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function request(PaymentRequestRequest $request): JsonResponse
    {
        try {
            $result = $this->paymentService->requestPayment($request->user()->id, $request->order_id);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function verify(PaymentVerifyRequest $request): JsonResponse
    {
        try {
            $payment = $this->paymentService->verifyPayment($request->user()->id, $request->payment_id);
            return response()->json([
                'message' => 'پرداخت با موفقیت انجام شد و وضعیت سفارش به پرداخت شده تغییر کرد.',
                'payment' => $payment
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}