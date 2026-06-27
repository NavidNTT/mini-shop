<?php

namespace Modules\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Payment\Services\PaymentService;
use Modules\Payment\Http\Requests\PaymentRequestRequest;
use Modules\Payment\Http\Requests\PaymentVerifyRequest;
use Modules\Payment\Transformers\PaymentResource;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function request(PaymentRequestRequest $request): JsonResponse
    {
        $result = $this->paymentService->requestPayment(
            $request->user()->id,
            $request->validated('order_id')
        );

        return response()->json([
            'message' => $result['message'],
            'data' => $result,
        ]);
    }

    public function verify(PaymentVerifyRequest $request): JsonResponse
    {
        $payment = $this->paymentService->verifyPayment(
            $request->user()->id,
            $request->validated('payment_id')
        );

        return response()->json([
            'message' => 'پرداخت با موفقیت انجام شد و وضعیت سفارش به پرداخت شده تغییر کرد.',
            'data' => new PaymentResource($payment),
        ]);
    }
}
