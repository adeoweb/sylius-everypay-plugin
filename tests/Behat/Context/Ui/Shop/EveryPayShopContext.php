<?php

declare(strict_types=1);

namespace Tests\AdeoWeb\SyliusEveryPayPlugin\Behat\Context\Ui\Shop;

use Behat\MinkExtension\Context\MinkContext;
use RuntimeException;
use Sylius\Behat\Page\Shop\Checkout\CompletePageInterface;
use Sylius\Behat\Page\Shop\Order\ShowPageInterface;
use Tests\AdeoWeb\SyliusEveryPayPlugin\Behat\Page\External\EveryPayPage;
use Tests\AdeoWeb\SyliusEveryPayPlugin\Behat\Service\Mocker\EveryPayApiMocker;

class EveryPayShopContext extends MinkContext
{
    public function __construct(
        private CompletePageInterface $summaryPage,
        private ShowPageInterface $orderDetails,
        private EveryPayPage $paymentPage,
        private EveryPayApiMocker $everyPayApiMocker,
    ) {
    }

    /**
     * @When I confirm my order with EveryPay payment
     *
     * @Given I have confirmed my order with EveryPay payment
     */
    public function iConfirmMyOrderWithEveryPayPayment(): void
    {
        $this->everyPayApiMocker->performActionInApiInitializeScope(function () {
            $this->summaryPage->confirmOrder();
        });
    }

    /**
     * @When I get redirected to EveryPay and complete my payment
     */
    public function iGetRedirectedToEveryPay(): void
    {
        $this->paymentPage->captureOrAuthorizeThenGoToAfterUrl();
    }

    /**
     * @When I get redirected to EveryPay and complete my payment using authorize
     */
    public function iGetRedirectedToEveryPayUsingAuthorize(): void
    {
        $this->paymentPage->captureOrAuthorizeThenGoToAfterUrl();
    }

    /**
     * @When I get redirected to EveryPay and complete my payment without webhook
     */
    public function iGetRedirectedToEveryPayWithoutWebhooks(): void
    {
        $this->paymentPage->captureOrAuthorizeThenGoToAfterUrl();
    }

    /**
     * @When I get redirected to EveryPay and complete my payment without webhook using authorize
     */
    public function iGetRedirectedToEveryPayWithoutWebhookUsingAuthorize(): void
    {
        $this->paymentPage->captureOrAuthorizeThenGoToAfterUrl();
    }

    /**
     * @Given I have clicked on "go back" during my EveryPay payment
     *
     * @When I click on "go back" during my EveryPay payment
     */
    public function iClickOnGoBackDuringMyEveryPayPayment(): void
    {
        $this->paymentPage->captureOrAuthorizeThenGoToAfterUrl();
    }

    /**
     * @When I try to pay again with EveryPay payment
     */
    public function iTryToPayAgainWithEveryPayPayment(): void
    {
        $this->orderDetails->pay();
    }

    /**
     * @Then I should be notified that my payment has been authorized
     */
    public function iShouldBeNotifiedThatMyPaymentHasBeenAuthorized(): void
    {
        $this->assertNotification('Payment has been authorized.');
    }

    private function assertNotification(string $expectedNotification): void
    {
        $notifications = $this->orderDetails->getNotifications();
        $hasNotifications = '';

        foreach ($notifications as $notification) {
            $hasNotifications .= $notification;
            if ($notification === $expectedNotification) {
                return;
            }
        }

        throw new RuntimeException(sprintf('There is no notification with "%s". Got "%s"', $expectedNotification, $hasNotifications));
    }
}
