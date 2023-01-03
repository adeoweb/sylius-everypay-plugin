<?php

declare(strict_types=1);

namespace AdeoWeb\SyliusEveryPayPlugin\Payum;

use AdeoWeb\SyliusEveryPayPlugin\Payum\Action\CaptureAction;
use AdeoWeb\SyliusEveryPayPlugin\Payum\Action\ConvertPaymentAction;
use AdeoWeb\SyliusEveryPayPlugin\Payum\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory as BaseFactory;

final class GatewayFactory extends BaseFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'everypay',
            'payum.factory_title' => 'EveryPay',
            'payum.action.capture' => new CaptureAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.status' => new StatusAction(),
        ]);

        $config['payum.required_options'] = [
            'api_url',
            'api_username',
            'api_secret',
            'processing_account',
            'httplug.message_factory',
            'httplug.client',
        ];

        if (!$config->offsetExists('payum.api')) {
            $config['payum.api'] = static function (ArrayObject $config): EveryPayApi {
                $config->validateNotEmpty($config['payum.required_options']);

                return new EveryPayApi(
                    messageFactory: $config['httplug.message_factory'],
                    client: $config['httplug.client'],
                    apiUrl: $config['api_url'] ?? '',
                    apiUsername: $config['api_username'] ?? '',
                    apiSecret: $config['api_secret'] ?? '',
                    processingAccount: $config['processing_account'] ?? '',
                );
            };
        }
    }
}
