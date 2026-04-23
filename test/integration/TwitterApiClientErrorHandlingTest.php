<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V1\Test\Integration;

use Horde\Service\Twitter\V1\TwitterApiClient;
use Horde\Service\Twitter\V1\TwitterApiConfig;
use Horde\Service\Twitter\V1\TwitterApiException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(TwitterApiClient::class)]
#[CoversClass(TwitterApiException::class)]
final class TwitterApiClientErrorHandlingTest extends TestCase
{
    public function testThrowsOnHttpError401(): void
    {
        $client = $this->buildClient(401, 'Unauthorized', '{}');

        $this->expectException(TwitterApiException::class);
        $this->expectExceptionMessage('401 Unauthorized');

        $client->verifyCredentials();
    }

    public function testThrowsOnHttpError404(): void
    {
        $client = $this->buildClient(404, 'Not Found', '{}');

        try {
            $client->showTweet(999);
            self::fail('Expected TwitterApiException');
        } catch (TwitterApiException $e) {
            self::assertSame(404, $e->httpStatusCode);
            self::assertStringContainsString('404 Not Found', $e->getMessage());
        }
    }

    public function testParsesTwitterErrorJson(): void
    {
        $body = json_encode([
            'errors' => [
                ['message' => 'Sorry, that page does not exist.', 'code' => 34],
            ],
        ]);

        $client = $this->buildClient(404, 'Not Found', $body);

        try {
            $client->showTweet(999);
            self::fail('Expected TwitterApiException');
        } catch (TwitterApiException $e) {
            self::assertStringContainsString('Sorry, that page does not exist.', $e->getMessage());
            self::assertSame($body, $e->responseBody);
        }
    }

    public function testParsesTwitterErrorStringField(): void
    {
        $body = json_encode(['error' => 'Not authorized.']);

        $client = $this->buildClient(401, 'Unauthorized', $body);

        try {
            $client->verifyCredentials();
            self::fail('Expected TwitterApiException');
        } catch (TwitterApiException $e) {
            self::assertStringContainsString('Not authorized.', $e->getMessage());
        }
    }

    public function testStaticCreateFactoryReturnsInstance(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->expects($this->never())->method('sendRequest');

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects($this->never())->method('createRequest');

        $client = TwitterApiClient::create($httpClient, $requestFactory);

        self::assertInstanceOf(TwitterApiClient::class, $client);
    }

    private function buildClient(int $statusCode, string $reasonPhrase, string $body): TwitterApiClient
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->atLeastOnce())->method('__toString')->willReturn($body);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->atLeastOnce())->method('getStatusCode')->willReturn($statusCode);
        $response->expects($this->atLeastOnce())->method('getReasonPhrase')->willReturn($reasonPhrase);
        $response->expects($this->atLeastOnce())->method('getBody')->willReturn($stream);

        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response);

        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->atLeastOnce())->method('withHeader')->willReturnSelf();

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects($this->once())->method('createRequest')->willReturn($request);

        return new TwitterApiClient(
            $httpClient,
            $requestFactory,
            new TwitterApiConfig(),
        );
    }
}
