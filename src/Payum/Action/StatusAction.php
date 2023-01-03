<?php

declare(strict_types=1);

namespace AdeoWeb\SyliusEveryPayPlugin\Payum\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

final class StatusAction implements ActionInterface
{
    /**
     * @param GetStatusInterface $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (0 === $details->count()) {
            $request->markNew();

            return;
        }

        if (isset($details['error'])) {
            $request->markFailed();

            return;
        }

        switch ($details['payment_state'] ?? null) {
            case 'initial':
                $request->markNew();

                break;
            case 'waiting_for_sca':
            case 'sent_for_processing':
            case 'waiting_for_3ds_response':
                $request->markPending();

                break;
            case 'settled':
                $request->markCaptured();

                break;
            case 'failed':
                $request->markFailed();

                break;
            case 'voided':
            case 'chargebacked':
                $request->markCanceled();

                break;
            case 'abandoned':
                $request->markExpired();

                break;
            case 'refunded':
                $request->markRefunded();

                break;
            default:
                $request->markUnknown();

                break;
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof ArrayAccess;
    }
}
