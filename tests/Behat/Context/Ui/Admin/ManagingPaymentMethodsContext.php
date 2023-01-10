<?php

declare(strict_types=1);

namespace Tests\AdeoWeb\SyliusEveryPayPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Tests\AdeoWeb\SyliusEveryPayPlugin\Behat\Page\Admin\PaymentMethod\CreatePageInterface;

class ManagingPaymentMethodsContext implements Context
{
    public function __construct(private CreatePageInterface $createPage)
    {
    }

    /**
     * @Given /^I want to create a new EveryPay payment method$/
     */
    public function iWantToCreateANewEveryPayPaymentMethod(): void
    {
        $this->createPage->open(['factory' => 'everypay']);
    }

    /**
     * @When I configure it with test EveryPay gateway data :apiUrl, :processingAccount, :apiUsername, :apiSecret
     */
    public function iConfigureItWithTestEveryPayGatewayData(string $apiUrl, string $processingAccount, string $apiUsername, string $apiSecret): void
    {
        $this->createPage->setApiUrl($apiUrl);
        $this->createPage->setProcessingAccount($processingAccount);
        $this->createPage->setApiUsername($apiUsername);
        $this->createPage->setApiSecret($apiSecret);
    }

    /**
     * @When I use authorize
     */
    public function iUseAuthorize(): void
    {
        $this->createPage->setEveryPayIsAuthorized(true);
    }

    /**
     * @When I don't use authorize
     */
    public function iDontUseAuthorize(): void
    {
        $this->createPage->setEveryPayIsAuthorized(false);
    }
}
