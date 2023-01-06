<?php

declare(strict_types=1);

namespace AdeoWeb\SyliusEveryPayPlugin\Doctrine\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Sylius\Component\Payment\Model\PaymentInterface;

interface PaymentRepositoryInterface
{
    /**
     * @throws NonUniqueResultException
     */
    public function findOneByEveryPayReference(string $reference): ?PaymentInterface;
}
