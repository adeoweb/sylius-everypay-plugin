<?php

declare(strict_types=1);

namespace AdeoWeb\SyliusEveryPayPlugin\Payum;

class EveryPayConfiguration
{
    public function __construct(
        private readonly string $apiUrl,
        private readonly string $apiUsername,
        private readonly string $apiSecret,
        private readonly string $processingAccount,
    ) {
    }

    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    public function getApiUsername(): string
    {
        return $this->apiUsername;
    }

    public function getApiSecret(): string
    {
        return $this->apiSecret;
    }

    public function getProcessingAccount(): string
    {
        return $this->processingAccount;
    }
}
