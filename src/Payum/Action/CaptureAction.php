<?php

declare(strict_types=1);

namespace AdeoWeb\SyliusEveryPayPlugin\Payum\Action;

use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Capture;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\HttpFoundation\Request;

final class CaptureAction extends AbstractAction
{
    private const ENDPOINT_URL = '/payments/oneoff';

    /**
     * @param Capture $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();
        $order = $payment->getOrder();
        $token = $request->getToken();

        $requestBody = $this->addDefaultBodyParameters(
            [
                'amount' => $payment->getAmount() / 100,
                'customer_url' => $token->getAfterUrl(),
                'order_reference' => $order->getNumber(),
                'email' => $order->getCustomer()->getEmail(),
                'customer_ip' => $order->getCustomerIp(),
                'integration_details' => self::INTEGRATION_DETAILS,
            ],
            $token,
        );

        $countryCode = $order->getBillingAddress()->getCountryCode();
        if (in_array($countryCode, self::ALLOWED_COUNTRIES, true)) {
            $requestBody['preferred_country'] = $countryCode;
        }

        $localeCode = substr($order->getLocaleCode(), 0, 2);
        if (in_array($localeCode, self::ALLOWED_LOCALES, true)) {
            $requestBody['locale'] = $localeCode;
        }

        $response = $this->doExecute(
            Request::METHOD_POST,
            self::ENDPOINT_URL,
            $requestBody,
        );

        $payment->setDetails($this->parseResponseBody($response));
    }

    public function supports($request): bool
    {
        return $request instanceof Capture && $request->getModel() instanceof PaymentInterface;
    }
}
