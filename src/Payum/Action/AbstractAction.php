<?php

declare(strict_types=1);

namespace AdeoWeb\SyliusEveryPayPlugin\Payum\Action;

use AdeoWeb\SyliusEveryPayPlugin\Payum\EveryPayConfiguration;
use DateTimeInterface;
use Http\Message\MessageFactory;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\RuntimeException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\HttpClientInterface;
use Payum\Core\Security\TokenInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

abstract class AbstractAction implements ActionInterface, ApiAwareInterface
{
    protected const ALLOWED_COUNTRIES = [
        'EE',
        'LT',
        'LV',
    ];

    protected const ALLOWED_LOCALES = [
        'cz',
        'da',
        'de',
        'en',
        'es',
        'et',
        'fi',
        'fr',
        'hu',
        'it',
        'lt',
        'lv',
        'nl',
        'no',
        'pl',
        'pt',
        'ru',
        'sk',
        'sv',
    ];

    protected const INTEGRATION_DETAILS = [
        'software' => 'Sylius',
        'version' => '0.1',
        'integration' => 'AdeoWeb Sylius EveryPay Plugin',
    ];

    protected EveryPayConfiguration $apiConfiguration;

    protected string $authorizationHeader;

    public function __construct(
        private readonly MessageFactory $messageFactory,
        private readonly HttpClientInterface $client,
    ) {
    }

    public function setApi($apiConfiguration): void
    {
        if (!$apiConfiguration instanceof EveryPayConfiguration) {
            throw new UnsupportedApiException('Not supported. Expected an instance of ' . EveryPayConfiguration::class);
        }

        $apiUrl = $apiConfiguration->getApiUrl();
        $apiUsername = $apiConfiguration->getApiUsername();
        $apiSecret = $apiConfiguration->getApiSecret();
        $accountName = $apiConfiguration->getProcessingAccount();

        if ('' === $apiUrl || '' === $apiUsername || '' === $apiSecret || '' === $accountName) {
            throw new InvalidArgumentException(
                'Configuration not valid: processing account, API URL, API username and/or API secret not set.',
            );
        }

        $this->apiConfiguration = $apiConfiguration;

        $this->authorizationHeader = $this->buildAuthorizationHeader();
    }

    /**
     * @param array<string, mixed> $body
     */
    protected function doExecute(
        string $httpMethod,
        string $endpointUrl,
        ?array $body = null,
    ): ResponseInterface {
        $request = $this->messageFactory->createRequest(
            $httpMethod,
            $this->buildFullEndpointUrl($endpointUrl),
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => $this->authorizationHeader,
            ],
            null === $body ? null : json_encode($body),
        );

        try {
            return $this->client->send($request);
        } catch (RuntimeException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new RuntimeException(previous: $exception);
        }
    }

    protected function buildFullEndpointUrl(string $methodUrl): string
    {
        $baseUrl = $this->apiConfiguration->getApiUrl();

        return rtrim($baseUrl, '/') . '/' . ltrim($methodUrl, '/');
    }

    /**
     * @param array<string, mixed> $json
     *
     * @return array<string, string>
     */
    protected function addDefaultBodyParameters(array $json, ?TokenInterface $token = null): array
    {
        $defaultJson = [
            'api_username' => $this->apiConfiguration->getApiUsername(),
            'account_name' => $this->apiConfiguration->getProcessingAccount(),
            'timestamp' => date(DateTimeInterface::ATOM),
        ];

        if (null !== $token) {
            $defaultJson['nonce'] = $token->getHash();
        }

        return array_replace($defaultJson, $json);
    }

    /**
     * @return array<int|string, mixed>|null
     */
    protected function parseResponseBody(ResponseInterface $response): ?array
    {
        return json_decode($response?->getBody()?->getContents() ?? '[]', true);
    }

    private function buildAuthorizationHeader(): string
    {
        return sprintf(
            'Basic %s',
            base64_encode(
                sprintf(
                    '%s:%s',
                    $this->apiConfiguration->getApiUsername(),
                    $this->apiConfiguration->getApiSecret(),
                ),
            ),
        );
    }
}
