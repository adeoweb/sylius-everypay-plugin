<?php

declare(strict_types=1);

namespace AdeoWeb\SyliusEveryPayPlugin\Payum\Action;

use AdeoWeb\SyliusEveryPayPlugin\Doctrine\Repository\PaymentRepositoryInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use Symfony\Component\HttpFoundation\Request;

final class NotifyAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;

    private const ENDPOINT_URL_MASK = '/payments/%s/?api_username=%s';

    private const QUERY_PARAM_PAYMENT_REFERENCE = 'payment_reference';

    public function __construct(
        private PaymentRepositoryInterface $paymentRepository,
    ) {
    }

    /**
     * @param Notify $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $paymentReference = $this->getPaymentReference();
        $payment = $this->paymentRepository->findOneByEveryPayReference($paymentReference);

        if (null === $payment) {
            throw new HttpResponse('Bad request', 400);
        }

        $paymentDetailsResponse = $this->api->performHttpRequest(
            Request::METHOD_GET,
            $this->buildEndpointUrl($paymentReference, $this->api->getApiUsername()),
        );

        $payment->setDetails($this->api->parseResponseBody($paymentDetailsResponse));

        $request->setModel($payment);
    }

    public function supports($request): bool
    {
        return $request instanceof Notify && null === $request->getModel();
    }

    private function getPaymentReference(): string
    {
        $this->gateway->execute($httpRequest = new GetHttpRequest());
        $query = $httpRequest->query;
        $paymentReference = (string) ($query[self::QUERY_PARAM_PAYMENT_REFERENCE] ?? '');

        if ('' === $paymentReference) {
            throw new HttpResponse('Bad request', 400);
        }

        return $paymentReference;
    }

    private function buildEndpointUrl(string $paymentReference, string $apiUsername): string
    {
        return sprintf(self::ENDPOINT_URL_MASK, $paymentReference, $apiUsername);
    }
}
