<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Integration;

use Horde\Service\Twitter\V2\CreateTweetParams;
use Horde\Service\Twitter\V2\RateLimitException;
use Horde\Service\Twitter\V2\TwitterApiClient;
use Horde\Service\Twitter\V2\TwitterApiConfig;
use Horde\Service\Twitter\V2\TwitterApiException;
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
#[CoversClass(RateLimitException::class)]
final class TwitterApiClientErrorTest extends TestCase
{
    public function testThrowsOnUnauthorized(): void
    {
        $client = $this->buildClient(401, '{"title":"Unauthorized","detail":"Invalid token"}');

        $this->expectException(TwitterApiException::class);
        $this->expectExceptionMessage('Invalid token');

        $client->getMe();
    }

    public function testThrowsOnNotFound(): void
    {
        $client = $this->buildClient(404, '{"title":"Not Found","detail":"Tweet not found"}');

        $this->expectException(TwitterApiException::class);
        $this->expectExceptionMessage('Tweet not found');

        $client->getTweet('999');
    }

    public function testParsesErrorsArray(): void
    {
        $json = json_encode([
            'errors' => [
                ['message' => 'Rate limit exceeded'],
            ],
        ]);

        $client = $this->buildClient(429, $json);

        $this->expectException(TwitterApiException::class);
        $this->expectExceptionMessage('Rate limit exceeded');

        $client->getMe();
    }

    public function testFallsBackToTitleIfNoDetail(): void
    {
        $json = json_encode(['title' => 'Forbidden']);

        $client = $this->buildClient(403, $json);

        $this->expectException(TwitterApiException::class);
        $this->expectExceptionMessage('Forbidden');

        $client->getMe();
    }

    public function testNonJsonErrorUsesBaseMessage(): void
    {
        $client = $this->buildClient(500, 'Internal Server Error');

        try {
            $client->getMe();
            self::fail('Expected TwitterApiException');
        } catch (TwitterApiException $e) {
            self::assertSame(500, $e->httpStatusCode);
            self::assertSame('Internal Server Error', $e->responseBody);
            self::assertStringContainsString('500', $e->getMessage());
        }
    }

    public function testStaticCreateFactory(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->expects($this->never())->method('sendRequest');

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects($this->never())->method('createRequest');

        $client = TwitterApiClient::create($httpClient, $requestFactory);

        self::assertInstanceOf(TwitterApiClient::class, $client);
    }

    // Gap 2: Error paths for write/delete methods

    public function testDeleteTweetThrowsOnError(): void
    {
        $client = $this->buildClient(403, '{"title":"Forbidden","detail":"Not authorized"}');

        $this->expectException(TwitterApiException::class);
        $this->expectExceptionMessage('Not authorized');

        $client->deleteTweet('123');
    }

    public function testCreateTweetThrowsOnError(): void
    {
        $client = $this->buildWriteClient(400, '{"detail":"Invalid request"}');

        $this->expectException(TwitterApiException::class);
        $this->expectExceptionMessage('Invalid request');

        $client->createTweet(new CreateTweetParams(text: 'test'));
    }

    public function testRetweetThrowsOnError(): void
    {
        $client = $this->buildWriteClient(403, '{"detail":"Already retweeted"}');

        $this->expectException(TwitterApiException::class);
        $this->expectExceptionMessage('Already retweeted');

        $client->retweet('42', '999');
    }

    public function testLikeTweetThrowsOnError(): void
    {
        $client = $this->buildWriteClient(403, '{"detail":"Already liked"}');

        $this->expectException(TwitterApiException::class);
        $this->expectExceptionMessage('Already liked');

        $client->likeTweet('42', '999');
    }

    public function testGetUserTimelineThrowsOnError(): void
    {
        $client = $this->buildClient(403, '{"title":"Forbidden"}');

        $this->expectException(TwitterApiException::class);
        $this->expectExceptionMessage('Forbidden');

        $client->getUserTimeline('42');
    }

    public function testGetBookmarksThrowsOnError(): void
    {
        $client = $this->buildClient(403, '{"title":"Forbidden"}');

        $this->expectException(TwitterApiException::class);
        $this->expectExceptionMessage('Forbidden');

        $client->getBookmarks('42');
    }

    // Gap 4: parseErrorResponse branches

    public function testParsesStringErrorsInArray(): void
    {
        $json = json_encode(['errors' => ['Bad request', 'Invalid field']]);

        $client = $this->buildClient(400, $json);

        try {
            $client->getMe();
            self::fail('Expected TwitterApiException');
        } catch (TwitterApiException $e) {
            self::assertStringContainsString('Bad request', $e->getMessage());
            self::assertStringContainsString('Invalid field', $e->getMessage());
        }
    }

    public function testDetailAndErrorsCombined(): void
    {
        $json = json_encode([
            'detail' => 'Forbidden',
            'errors' => [['message' => 'Scope missing']],
        ]);

        $client = $this->buildClient(403, $json);

        try {
            $client->getMe();
            self::fail('Expected TwitterApiException');
        } catch (TwitterApiException $e) {
            self::assertStringContainsString('Forbidden', $e->getMessage());
            self::assertStringContainsString('Scope missing', $e->getMessage());
        }
    }

    public function testEmptyJsonObjectUsesBaseMessage(): void
    {
        $client = $this->buildClient(422, '{}');

        try {
            $client->getMe();
            self::fail('Expected TwitterApiException');
        } catch (TwitterApiException $e) {
            self::assertSame(422, $e->httpStatusCode);
            self::assertStringContainsString('422', $e->getMessage());
            self::assertStringContainsString('Error', $e->getMessage());
        }
    }

    public function testRateLimitResponseYieldsRateLimitException(): void
    {
        $client = $this->buildClient(
            429,
            '{"detail":"rate limited"}',
            headers: [
                'x-rate-limit-limit' => '900',
                'x-rate-limit-remaining' => '0',
                'x-rate-limit-reset' => '1717000000',
            ],
        );

        try {
            $client->getMe();
            self::fail('Expected RateLimitException');
        } catch (RateLimitException $e) {
            self::assertSame(429, $e->httpStatusCode);
            self::assertSame(900, $e->limit);
            self::assertSame(0, $e->remaining);
            self::assertNotNull($e->resetAt);
            self::assertSame(1717000000, $e->resetAt->getTimestamp());
            self::assertStringContainsString('rate limited', $e->getMessage());
        }
    }

    /**
     * @param array<string, string> $headers
     */
    private function buildClient(int $statusCode, string $body, array $headers = []): TwitterApiClient
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->atLeastOnce())->method('__toString')->willReturn($body);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->atLeastOnce())->method('getStatusCode')->willReturn($statusCode);
        $response->expects($this->atLeastOnce())->method('getReasonPhrase')->willReturn('Error');
        $response->expects($this->atLeastOnce())->method('getBody')->willReturn($stream);
        $response->method('getHeaderLine')->willReturnCallback(
            static fn (string $name): string => $headers[strtolower($name)] ?? '',
        );

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

    private function buildWriteClient(int $statusCode, string $body): TwitterApiClient
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->atLeastOnce())->method('__toString')->willReturn($body);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->atLeastOnce())->method('getStatusCode')->willReturn($statusCode);
        $response->expects($this->atLeastOnce())->method('getReasonPhrase')->willReturn('Error');
        $response->expects($this->atLeastOnce())->method('getBody')->willReturn($stream);

        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response);

        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->atLeastOnce())->method('withHeader')->willReturnSelf();
        $request->method('withBody')->willReturnSelf();

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects($this->once())->method('createRequest')->willReturn($request);

        $bodyStream = $this->createMock(StreamInterface::class);
        $bodyStream->expects($this->never())->method($this->anything());

        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory->expects($this->once())
            ->method('createStream')
            ->willReturn($bodyStream);

        return new TwitterApiClient(
            $httpClient,
            $requestFactory,
            new TwitterApiConfig(),
            $streamFactory,
        );
    }
}
