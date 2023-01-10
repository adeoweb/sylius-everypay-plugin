<?php

declare(strict_types=1);

namespace Tests\AdeoWeb\SyliusEveryPayPlugin\Behat\Service\Mocker;

use Http\Client\HttpClient;
use Mockery\MockInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Sylius\Behat\Service\Mocker\MockerInterface;

final class EveryPayApiMocker
{
    public function __construct(
        private MockerInterface $mocker,
    ) {
    }

    public function performActionInApiInitializeScope(callable $action)
    {
        $this->mockApiPaymentInitializeResponse();
        $action();
        $this->mocker->unmockAll();
    }

    public function performActionInApiSuccessfulScope(callable $action)
    {
        $this->mockApiSuccessfulPaymentResponse();
        $action();
        $this->mocker->unmockAll();
    }

    private function mockApiSuccessfulPaymentResponse()
    {
        $mockedResponse = $this->responseLoader->getMockedResponse('EveryPay/everypay_api_successful_payment.json');
        $firstGetExpressCheckoutDetailsStream = $this->mockStream($mockedResponse['firstGetExpressCheckoutDetails']);
        $firstGetExpressCheckoutDetailsResponse = $this->mockHttpResponse(200, $firstGetExpressCheckoutDetailsStream);

        $doExpressCheckoutPaymentStream = $this->mockStream($mockedResponse['doExpressCheckoutPayment']);
        $doExpressCheckoutPaymentResponse = $this->mockHttpResponse(200, $doExpressCheckoutPaymentStream);

        $secondGetExpressCheckoutDetailsStream = $this->mockStream($mockedResponse['secondGetExpressCheckoutDetails']);
        $secondGetExpressCheckoutDetailsResponse = $this->mockHttpResponse(200, $secondGetExpressCheckoutDetailsStream);

        $getTransactionDetailsStream = $this->mockStream($mockedResponse['getTransactionDetails']);
        $getTransactionDetailsResponse = $this->mockHttpResponse(200, $getTransactionDetailsStream);

        $this->mocker->mockService('adeo_web.sylius_everypay_plugin.payum.httplug_client', HttpClient::class)
            ->expects('send')
            ->times(4)
            ->andReturns($firstGetExpressCheckoutDetailsResponse, $doExpressCheckoutPaymentResponse, $secondGetExpressCheckoutDetailsResponse, $getTransactionDetailsResponse)
        ;
    }

    private function mockApiPaymentInitializeResponse()
    {
        $setPaymentOneOff = $this->mockStream([
            'payment_link' => 'https://www.everypay.gr/',
            'payment_reference' => 'everypay_payment_1',
            'payment_state' => 'initial'
        ]);
        $setPaymentOneOffResponse = $this->mockHttpResponse(200, $setPaymentOneOff);

        $this->mocker->mockService('adeo_web.sylius_everypay_plugin.payum.httplug_client', HttpClient::class)
            ->expects('sendRequest')
            ->once()
            ->andReturn($setPaymentOneOffResponse)
        ;
    }

    private function mockStream(array $content): MockInterface
    {
        $mockedStream = $this->mocker->mockCollaborator(StreamInterface::class);
        $mockedStream->shouldReceive('getContents')->once()->andReturn(json_encode($content, JSON_THROW_ON_ERROR));
        $mockedStream->shouldReceive('close')->once()->andReturn();

        return $mockedStream;
    }

    private function mockHttpResponse(int $statusCode, MockInterface $streamMock): MockInterface
    {
        $mockedHttpResponse = $this->mocker->mockCollaborator(ResponseInterface::class);
        $mockedHttpResponse->shouldReceive('getStatusCode')->once()->andReturn($statusCode);
        $mockedHttpResponse->shouldReceive('getBody')->once()->andReturn($streamMock);

        return $mockedHttpResponse;
    }
}
