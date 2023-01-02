<?php

declare(strict_types=1);

namespace AdeoWeb\SyliusEveryPayPlugin\Payum\Action;

use AdeoWeb\SyliusEveryPayPlugin\Payum\EveryPayApi;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Convert;
use Sylius\Component\Core\Model\PaymentInterface;

final class ConvertPaymentAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    /**
     * @param Convert $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();
        $details = $payment->getDetails();

        if ([] === $details) {
            $details = $this->buildDetailsForCapture($request, $payment);
        }

        $request->setResult($details);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getTo() === 'array'
        ;
    }

    private function buildDetailsForCapture(Convert $request, PaymentInterface $payment): array
    {
        $order = $payment->getOrder();
        $token = $request->getToken();

        $details = $this->api->addDefaultBodyParameters(
            [
                'amount' => $payment->getAmount() / 100,
                'customer_url' => $token->getAfterUrl(),
                'order_reference' => $order->getNumber(),
                'email' => $order->getCustomer()->getEmail(),
                'customer_ip' => $order->getCustomerIp(),
                'integration_details' => EveryPayApi::INTEGRATION_DETAILS,
            ],
            $token,
        );

        $countryCode = $order->getBillingAddress()->getCountryCode();
        if (in_array($countryCode, EveryPayApi::ALLOWED_COUNTRIES, true)) {
            $details['preferred_country'] = $countryCode;
        }

        $localeCode = substr($order->getLocaleCode(), 0, 2);
        if (in_array($localeCode, EveryPayApi::ALLOWED_LOCALES, true)) {
            $details['locale'] = $localeCode;
        }

        return $details;
    }
}
