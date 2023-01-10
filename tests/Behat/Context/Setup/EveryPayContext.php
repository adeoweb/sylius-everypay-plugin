<?php

declare(strict_types=1);

namespace Tests\AdeoWeb\SyliusEveryPayPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ExampleFactoryInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Webmozart\Assert\Assert;

class EveryPayContext implements Context
{
    public function __construct(
        private SharedStorageInterface $sharedStorage,
        private PaymentMethodRepositoryInterface $paymentMethodRepository,
        private ExampleFactoryInterface $paymentMethodExampleFactory,
        private EntityManagerInterface $paymentMethodManager,
    ) {
    }

    /**
     * @Given the store has a payment method :paymentMethodName with a code :paymentMethodCode and EveryPay payment gateway
     * @Given the store has a payment method :paymentMethodName with a code :paymentMethodCode and EveryPay payment gateway without using authorize
     */
    public function theStoreHasAPaymentMethodWithACodeAndEveryPayPaymentGateway(
        string $paymentMethodName,
        string $paymentMethodCode,
        bool $useAuthorize = false
    ): void {
        $paymentMethod = $this->createPaymentMethodEveryPay(
            $paymentMethodName,
            $paymentMethodCode,
            'everypay',
            'EveryPay'
        );

        $gatewayConfig = $paymentMethod->getGatewayConfig();
        Assert::notNull($gatewayConfig);

        $gatewayConfig->setConfig([
            'api_url' => 'https://sandbox-api.everypay.gr',
            'api_username' => 'TEST',
            'api_secret' => 'TEST',
            'processing_account' => 'TEST',
            'httplug.client' => '@adeo_web.sylius_everypay_plugin.payum.httplug_client',
            'use_authorize' => $useAuthorize,
        ]);
        $this->paymentMethodManager->flush();
    }

    /**
     * @Given the store has a payment method :paymentMethodName with a code :paymentMethodCode and EveryPay payment gateway using authorize
     */
    public function theStoreHasAPaymentMethodWithACodeAndEveryPayPaymentGatewayUsingAuthorize(
        string $paymentMethodName,
        string $paymentMethodCode
    ): void {
        $this->theStoreHasAPaymentMethodWithACodeAndEveryPayPaymentGateway($paymentMethodName, $paymentMethodCode, true);
    }

    private function createPaymentMethodEveryPay(
        string $name,
        string $code,
        string $factoryName,
        string $description = '',
        bool $addForCurrentChannel = true,
        int $position = null
    ): PaymentMethodInterface {
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $this->paymentMethodExampleFactory->create([
            'name' => ucfirst($name),
            'code' => $code,
            'description' => $description,
            'gatewayName' => $factoryName,
            'gatewayFactory' => $factoryName,
            'enabled' => true,
            'channels' => ($addForCurrentChannel && $this->sharedStorage->has('channel')) ? [$this->sharedStorage->get('channel')] : [],
        ]);
        if (null !== $position) {
            $paymentMethod->setPosition($position);
        }
        $this->sharedStorage->set('payment_method', $paymentMethod);
        $this->paymentMethodRepository->add($paymentMethod);

        return $paymentMethod;
    }
}
