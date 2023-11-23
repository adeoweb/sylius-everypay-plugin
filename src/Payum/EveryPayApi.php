<?php

declare(strict_types=1);

namespace AdeoWeb\SyliusEveryPayPlugin\Payum;

use DateTimeInterface;
use Http\Message\MessageFactory;
use Payum\Core\Exception\RuntimeException;
use Payum\Core\Security\TokenInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Webmozart\Assert\Assert;

class EveryPayApi
{
    public const ALLOWED_COUNTRIES = [
        'EE',
        'LT',
        'LV',
    ];

    public const ALLOWED_LOCALES = [
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

    public const INTEGRATION_DETAILS = [
        'software' => 'Sylius',
        'version' => '0.2',
        'integration' => 'AdeoWeb Sylius EveryPay Plugin',
    ];

    public const QUERY_PARAM_PAYMENT_REFERENCE = 'payment_reference';

    protected string $authorizationHeader;

    public function __construct(
        private MessageFactory $messageFactory,
        private ClientInterface $client,
        private string $apiUrl,
        private string $apiUsername,
        private string $apiSecret,
        private string $processingAccount,
    ) {
        Assert::allStringNotEmpty(
            [$this->apiUrl, $this->apiUsername, $this->apiSecret, $this->processingAccount],
            'Configuration not valid: processing account, API URL, API username and API secret must not be empty.',
        );

        $this->authorizationHeader = $this->buildAuthorizationHeader();
    }

    public function getApiUsername(): string
    {
        return $this->apiUsername;
    }

    /**
     * @param array<string, mixed> $body
     */
    public function performHttpRequest(
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
            return $this->client->sendRequest($request);
        } catch (RuntimeException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new RuntimeException(previous: $exception);
        }
    }

    /**
     * @param array<string, mixed> $body
     *
     * @return array<string, string>
     */
    public function addDefaultBodyParameters(array $body, ?TokenInterface $token = null): array
    {
        $defaultBody = [
            'api_username' => $this->getApiUsername(),
            'account_name' => $this->processingAccount,
            'timestamp' => date(DateTimeInterface::ATOM),
        ];

        if (null !== $token) {
            $defaultBody['nonce'] = $token->getHash();
        }

        return array_replace($defaultBody, $body);
    }

    protected function buildFullEndpointUrl(string $methodUrl): string
    {
        $baseUrl = $this->apiUrl;

        return rtrim($baseUrl, '/') . '/' . ltrim($methodUrl, '/');
    }

    /**
     * @return array<int|string, mixed>|null
     */
    public function parseResponseBody(ResponseInterface $response): ?array
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
                    $this->getApiUsername(),
                    $this->apiSecret,
                ),
            ),
        );
    }
}
