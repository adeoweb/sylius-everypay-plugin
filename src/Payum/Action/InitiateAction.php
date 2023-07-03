<?php

declare(strict_types=1);

namespace AdeoWeb\SyliusEveryPayPlugin\Payum\Action;

use ArrayAccess;
use AdeoWeb\SyliusEveryPayPlugin\Payum\Request\Initiate;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Symfony\Component\HttpFoundation\Request;

class InitiateAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    private const ENDPOINT_URL = '/payments/oneoff';

    use ApiAwareTrait;
    use GatewayAwareTrait;

    /**
     * @param Initiate $request
     */
    public function execute($request): void
    {
        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->updateModelForCapture($model);
    }

    public function supports($request): bool
    {
        return $request instanceof Initiate && $request->getModel() instanceof ArrayAccess;
    }

    private function updateModelForCapture(ArrayObject $model): void
    {
        // TODO: maybe fill in missing values with fallback ones (or not)

        $response = $this->api->performHttpRequest(
            Request::METHOD_POST,
            self::ENDPOINT_URL,
            $model->getArrayCopy(),
        );

        $details = $this->api->parseResponseBody($response);

        $model->exchangeArray($details);
    }
}
