<?php

declare(strict_types=1);

namespace Tests\AdeoWeb\SyliusEveryPayPlugin\Behat\Page\Admin\PaymentMethod;

use Sylius\Behat\Page\Admin\PaymentMethod\CreatePageInterface as BaseCreatePageInterface;

interface CreatePageInterface extends BaseCreatePageInterface
{
    public function setApiUrl(string $apiUrl): void;

    public function setProcessingAccount(string $ProcessingAccount): void;

    public function setApiUsername(string $setApiUsername): void;

    public function setApiSecret(string $setApiSecret): void;

    public function setEveryPayIsAuthorized(bool $isAuthorized): void;
}
