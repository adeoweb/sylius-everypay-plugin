<?php

declare(strict_types=1);

namespace AdeoWeb\SyliusEveryPayPlugin\Payum;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory as BaseFactory;

final class GatewayFactory extends BaseFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'everypay',
            'payum.factory_title' => 'EveryPay',
        ]);

        $config['payum.api'] = static fn (ArrayObject $config): EveryPayConfiguration => new EveryPayConfiguration(
            $config['api_url'] ?? '',
            $config['api_username'] ?? '',
            $config['api_secret'] ?? '',
            $config['processing_account'] ?? '',
        );
    }
}
