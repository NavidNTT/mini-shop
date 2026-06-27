<?php

namespace Modules\Payment\Gateways;

class PaymentResponse
{
    public function __construct(
        private readonly bool $success,
        private readonly string $transactionId,
        private readonly string $message,
        private readonly ?string $redirectUrl = null,
        private readonly array $metadata = []
    ) {}

    public function isSuccessful(): bool
    {
        return $this->success;
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }
}
