<?php

declare(strict_types=1);

namespace AdeoWeb\SyliusEveryPayPlugin\Extension;

use AdeoWeb\SyliusEveryPayPlugin\Payum\EveryPayApi;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Request\Notify;
use Exception;

final class NotifyPaymentStateExtension implements ExtensionInterface
{
    use GatewayAwareTrait;

    private bool $alreadyNotified = false;

    public function __construct(private Payum $payum) {
    }

    /**
     * @throws Exception
     */
    public function onPreExecute(Context $context): void
    {
    }

    public function onExecute(Context $context): void
    {
        if ($this->alreadyNotified) {
            return;
        }

        $request = $context->getRequest();

        if (!$request instanceof GetStatusInterface || !$this->getPaymentReference($context->getGateway())) {
            return;
        }

        $notify = new Notify(null);
        $this->alreadyNotified = true;
        $context->getGateway()->execute($notify);
    }

    public function onPostExecute(Context $context): void
    {
        $previousStack = $context->getPrevious();
        if (!count($previousStack)){
            $this->alreadyNotified = false;
        }
    }

    private function getPaymentReference(GatewayInterface $gateway): bool
    {
        $gateway->execute($httpRequest = new GetHttpRequest());
        $query = $httpRequest->query;

        return (bool) ($query[EveryPayApi::QUERY_PARAM_PAYMENT_REFERENCE] ?? false);
    }
}
