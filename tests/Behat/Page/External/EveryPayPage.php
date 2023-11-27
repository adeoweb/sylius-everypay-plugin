<?php

declare(strict_types=1);

namespace Tests\AdeoWeb\SyliusEveryPayPlugin\Behat\Page\External;

use ArrayAccess;
use Behat\Mink\Session;
use FriendsOfBehat\PageObjectExtension\Page\Page;
use Payum\Core\Security\TokenInterface;
use RuntimeException;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpKernel\HttpKernelBrowser;
use Tests\AdeoWeb\SyliusEveryPayPlugin\Behat\Page\Shop\PayumNotifyPageInterface;

final class EveryPayPage extends Page implements EveryPayPageInterface
{
    private array $deadTokens = [];

    /**
     * @param array|ArrayAccess $minkParameters
     */
    public function __construct(
        Session $session,
        $minkParameters,
        private RepositoryInterface $securityTokenRepository,
        private HttpKernelBrowser $client,
        private PayumNotifyPageInterface $payumNotifyPage,
    ) {
        parent::__construct($session, $minkParameters);
    }

    public function captureOrAuthorizeThenGoToAfterUrl(): void
    {
        try {
            $token = $this->findToken();
        } catch (RuntimeException $e) {
            // No easy way to know if we need authorize or not
            $token = $this->findToken('authorize');
        }

        // Capture or Authorize
        $this->getDriver()->visit($token->getTargetUrl());

        $this->getDriver()->visit($token->getAfterUrl());
    }

    public function notify(string $content): void
    {
        $notifyToken = $this->findToken('notify');

        $notifyUrl = $this->payumNotifyPage->getNotifyUrl([
            'gateway' => 'everypay',
        ]);

        $payload = sprintf($content, $notifyToken->getHash());
        $this->client->request(
            'POST',
            $notifyUrl,
            [],
            [],
            [],
            $payload
        );
    }

    private function findToken(string $type = 'capture'): TokenInterface
    {
        $foundToken = null;
        /** @var TokenInterface[] $tokens */
        $tokens = $this->securityTokenRepository->findAll();
        foreach ($tokens as $token) {
            if (in_array($token->getHash(), $this->deadTokens, true)) {
                continue;
            }

            if (!str_contains($token->getTargetUrl(), $type)) {
                continue;
            }

            $foundToken = $token;
        }

        if (null === $foundToken) {
            throw new RuntimeException('Cannot find token, check if you are after proper checkout steps');
        }

        // Sometimes the token found is an already consumed one. Here we compare
        // the $foundToken->getAfterUrl() with all tokens to see if the token
        // concerned by the after url is alive, if not we save it to a dead list
        // and retry to found the right token
        if ($type !== 'notify') {
            $relatedToken = null;
            foreach ($tokens as $token) {
                if (!str_contains($foundToken->getAfterUrl(), $token->getHash())) {
                    continue;
                }
                $relatedToken = $token;
            }

            if (null === $relatedToken) {
                $this->deadTokens[] = $foundToken->getHash();

                return $this->findToken($type);
            }
        }

        return $foundToken;
    }

    protected function getUrl(array $urlParameters = []): string
    {
        return 'https://www.everypay.gr';
    }
}
