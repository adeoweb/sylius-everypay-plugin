<?php

declare(strict_types=1);

namespace Tests\AdeoWeb\SyliusEveryPayPlugin\Behat\Page\Admin\PaymentMethod;

use Behat\Mink\Exception\ElementNotFoundException;
use Sylius\Behat\Page\Admin\PaymentMethod\CreatePage as BaseCreatePage;

final class CreatePage extends BaseCreatePage implements CreatePageInterface
{
    public function setApiUrl(string $apiUrl): void
    {
        $this->getDocument()->fillField('API URL', $apiUrl);
    }

    public function setProcessingAccount(string $ProcessingAccount): void
    {
        $this->getDocument()->fillField('Processing account', $ProcessingAccount);
    }

    public function setApiUsername(string $setApiUsername): void
    {
        $this->getDocument()->fillField('API username', $setApiUsername);
    }

    public function setApiSecret(string $setApiSecret): void
    {
        $this->getDocument()->fillField('API secret', $setApiSecret);
    }

    /**
     * @throws ElementNotFoundException
     */
    public function setEveryPayIsAuthorized(bool $isAuthorized): void
    {
        if ($isAuthorized) {
            $this->getDocument()->checkField('Use authorize');
        } else {
            $this->getDocument()->uncheckField('Use authorize');
        }
    }
}
