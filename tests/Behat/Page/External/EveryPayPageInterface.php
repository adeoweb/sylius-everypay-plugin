<?php

declare(strict_types=1);

namespace Tests\AdeoWeb\SyliusEveryPayPlugin\Behat\Page\External;

use FriendsOfBehat\PageObjectExtension\Page\PageInterface;

interface EveryPayPageInterface extends PageInterface
{
    public function captureOrAuthorizeThenGoToAfterUrl(): void;

    public function notify(string $content): void;
}
