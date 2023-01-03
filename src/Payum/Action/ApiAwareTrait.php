<?php

declare(strict_types=1);

namespace AdeoWeb\SyliusEveryPayPlugin\Payum\Action;

use AdeoWeb\SyliusEveryPayPlugin\Payum\EveryPayApi;
use Payum\Core\Exception\UnsupportedApiException;

trait ApiAwareTrait
{
    protected EveryPayApi $api;

    /**
     * @param EveryPayApi $api
     */
    public function setApi($api): void
    {
        if (!$api instanceof EveryPayApi) {
            throw new UnsupportedApiException(
                sprintf(
                    'Unsupported api given. It must be an instance of %s.',
                    EveryPayApi::class,
                ),
            );
        }

        $this->api = $api;
    }
}
