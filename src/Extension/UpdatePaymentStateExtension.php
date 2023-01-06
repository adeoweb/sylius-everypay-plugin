<?php

declare(strict_types=1);

namespace AdeoWeb\SyliusEveryPayPlugin\Extension;

use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Storage\IdentityInterface;
use Payum\Core\Storage\StorageInterface;
use SM\Factory\FactoryInterface;
use SM\SMException;
use Sylius\Bundle\PayumBundle\Factory\GetStatusFactoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Sylius\Component\Resource\StateMachine\StateMachineInterface;
use Webmozart\Assert\Assert;

/**
 * Reproduction of the Payum Core StorageExtension behaviour for Sylius payments
 * Copied from flux-se/sylius-payum-stripe-plugin
 *
 * @see https://github.com/FLUX-SE/SyliusPayumStripePlugin/blob/master/src/Extension/UpdatePaymentStateExtension.php
 * @see \Payum\Core\Extension\StorageExtension
 */
final class UpdatePaymentStateExtension implements ExtensionInterface
{
    /** @var PaymentInterface[] */
    private array $scheduledPaymentsToProcess = [];

    public function __construct(
        private FactoryInterface $factory,
        private StorageInterface $storage,
        private GetStatusFactoryInterface $getStatusRequestFactory,
    ) {
    }

    public function onPreExecute(Context $context): void
    {
        $request = $context->getRequest();

        if (!$request instanceof ModelAggregateInterface) {
            return;
        }

        $payment = $request->getModel();

        if ($payment instanceof IdentityInterface) {
            $payment = $this->storage->find($request->getModel());
        }

        if (!$payment instanceof PaymentInterface) {
            return;
        }

        $this->scheduleForProcessingIfSupported($payment);
    }

    public function onExecute(Context $context): void
    {
    }

    /**
     * @throws SMException
     */
    public function onPostExecute(Context $context): void
    {
        if (null !== $context->getException()) {
            return;
        }

        $request = $context->getRequest();

        if ($request instanceof ModelAggregateInterface) {
            $payment = $request->getModel();
            if ($payment instanceof PaymentInterface) {
                $this->scheduleForProcessingIfSupported($payment);
            }
        }

        if (count($context->getPrevious()) > 0) {
            return;
        }

        // Process scheduled payments only when we are post executing a
        // root payum request
        foreach ($this->scheduledPaymentsToProcess as $id => $payment) {
            $this->processPayment($context, $payment);
            unset($this->scheduledPaymentsToProcess[$id]);
        }
    }

    /**
     * @throws SMException
     */
    private function processPayment(Context $context, PaymentInterface $payment): void
    {
        $status = $this->getStatusRequestFactory->createNewWithModel($payment);
        $context->getGateway()->execute($status);
        /** @var string $value */
        $value = $status->getValue();
        if ($payment->getState() === $value) {
            return;
        }

        if (PaymentInterface::STATE_UNKNOWN === $value) {
            return;
        }

        $this->updatePaymentState($payment, $value);
    }

    /**
     * @throws SMException
     */
    private function updatePaymentState(PaymentInterface $payment, string $nextState): void
    {
        $stateMachine = $this->factory->get($payment, PaymentTransitions::GRAPH);

        Assert::isInstanceOf($stateMachine, StateMachineInterface::class);

        $transition = $stateMachine->getTransitionToState($nextState);

        if (null !== $transition) {
            $stateMachine->apply($transition);
        }
    }

    private function scheduleForProcessingIfSupported(PaymentInterface $payment): void
    {
        $id = $payment->getId();

        if (is_int($id)) {
            $this->scheduledPaymentsToProcess[$id] = $payment;
        }
    }
}
