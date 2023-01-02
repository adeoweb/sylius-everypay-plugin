<?php

declare(strict_types=1);

namespace AdeoWeb\SyliusEveryPayPlugin\Doctrine\Repository;

use Sylius\Component\Payment\Model\PaymentInterface;

interface PaymentRepositoryInterface
{
    public function findOneByEveryPayReference(string $reference): ?PaymentInterface;
}
