<?php

declare(strict_types=1);

namespace AdeoWeb\SyliusEveryPayPlugin\Doctrine\Repository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\NonUniqueResultException;
use Sylius\Component\Payment\Model\PaymentInterface;

trait PaymentRepositoryTrait
{
    /**
     * @throws NonUniqueResultException
     */
    public function findOneByEveryPayReference(string $reference): ?PaymentInterface
    {
        return $this->createQueryBuilder('p')
            ->orWhere('JSON_CONTAINS(p.details, :reference, :jsonPath) = 1')
            ->setParameter('reference', json_encode($reference), Types::STRING)
            ->setParameter('jsonPath', '$."payment_reference"', Types::STRING)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
