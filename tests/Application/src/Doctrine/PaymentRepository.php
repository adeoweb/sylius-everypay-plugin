<?php

declare(strict_types=1);

namespace Tests\AdeoWeb\SyliusEveryPayPlugin\App\Doctrine;

use AdeoWeb\SyliusEveryPayPlugin\Doctrine\Repository\PaymentRepositoryInterface;
use AdeoWeb\SyliusEveryPayPlugin\Doctrine\Repository\PaymentRepositoryTrait;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\PaymentRepository as BasePaymentRepository;

final class PaymentRepository extends BasePaymentRepository implements PaymentRepositoryInterface
{
    use PaymentRepositoryTrait;
}
