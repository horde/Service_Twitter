<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Integration;

use Horde\Service\Twitter\V2\CreateTweetParams;
use Horde\Service\Twitter\V2\Tweet;
use Horde\Service\Twitter\V2\TwitterApiClient;
use Horde\Service\Twitter\V2\TwitterApiConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(TwitterApiClient::class)]
#[CoversClass(Tweet::class)]
final class TwitterApiClientWriteTest extends TestCase
{
    public function testCreateTweetReturnsTweet(): void
    {
        $json = json_encode([
            'data' => [
                'id' => '123',
                'text' => 'New tweet',
            ],
        ]);

        $client = $this->buildClient(201, $json, withStreamFactory: true, readsBody: true);
        $tweet = $client->createTweet(new CreateTweetParams(text: 'New tweet'));

        self::assertSame('123', $tweet->id);
        self::assertSame('New tweet', $tweet->text);
    }

    public function testDeleteTweetReturnsVoid(): void
    {
        $json = json_encode(['data' => ['deleted' => true]]);

        $client = $this->buildClient(200, $json);
        $client->deleteTweet('123');

        $this->addToAssertionCount(1);
    }

    public function testRetweetReturnsVoid(): void
    {
        $json = json_encode(['data' => ['retweeted' => true]]);

        $client = $this->buildClient(200, $json, withStreamFactory: true);
        $client->retweet('42', '999');

        $this->addToAssertionCount(1);
    }

    public function testUndoRetweetReturnsVoid(): void
    {
        $json = json_encode(['data' => ['retweeted' => false]]);

        $client = $this->buildClient(200, $json);
        $client->undoRetweet('42', '999');

        $this->addToAssertionCount(1);
    }

    public function testLikeTweetReturnsVoid(): void
    {
        $json = json_encode(['data' => ['liked' => true]]);

        $client = $this->buildClient(200, $json, withStreamFactory: true);
        $client->likeTweet('42', '999');

        $this->addToAssertionCount(1);
    }

    public function testUnlikeTweetReturnsVoid(): void
    {
        $json = json_encode(['data' => ['liked' => false]]);

        $client = $this->buildClient(200, $json);
        $client->unlikeTweet('42', '999');

        $this->addToAssertionCount(1);
    }

    public function testAddBookmarkReturnsVoid(): void
    {
        $json = json_encode(['data' => ['bookmarked' => true]]);

        $client = $this->buildClient(200, $json, withStreamFactory: true);
        $client->addBookmark('42', '555');

        $this->addToAssertionCount(1);
    }

    public function testCreateTweetThrowsWithoutStreamFactory(): void
    {
        $client = $this->buildClientWithoutStreamFactory();

        $this->expectException(\Horde\Service\Twitter\V2\TwitterApiException::class);
        $this->expectExceptionMessage('StreamFactoryInterface is required');

        $client->createTweet(new CreateTweetParams(text: 'test'));
    }

    public function testRetweetThrowsWithoutStreamFactory(): void
    {
        $client = $this->buildClientWithoutStreamFactory();

        $this->expectException(\Horde\Service\Twitter\V2\TwitterApiException::class);
        $this->expectExceptionMessage('StreamFactoryInterface is required');

        $client->retweet('42', '999');
    }

    public function testLikeTweetThrowsWithoutStreamFactory(): void
    {
        $client = $this->buildClientWithoutStreamFactory();

        $this->expectException(\Horde\Service\Twitter\V2\TwitterApiException::class);
        $this->expectExceptionMessage('StreamFactoryInterface is required');

        $client->likeTweet('42', '999');
    }

    public function testAddBookmarkThrowsWithoutStreamFactory(): void
    {
        $client = $this->buildClientWithoutStreamFactory();

        $this->expectException(\Horde\Service\Twitter\V2\TwitterApiException::class);
        $this->expectExceptionMessage('StreamFactoryInterface is required');

        $client->addBookmark('42', '555');
    }

    private function buildClientWithoutStreamFactory(): TwitterApiClient
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->expects($this->never())->method('sendRequest');

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects($this->never())->method('createRequest');

        return new TwitterApiClient($httpClient, $requestFactory, new TwitterApiConfig());
    }

    private function buildClient(
        int $statusCode,
        string $body,
        bool $withStreamFactory = false,
        bool $readsBody = false,
    ): TwitterApiClient {
        $responseStream = $this->createMock(StreamInterface::class);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('getStatusCode')->willReturn($statusCode);

        if ($readsBody) {
            $responseStream->expects($this->atLeastOnce())->method('__toString')->willReturn($body);
            $response->expects($this->atLeastOnce())->method('getBody')->willReturn($responseStream);
        } else {
            $responseStream->expects($this->never())->method($this->anything());
            $response->expects($this->never())->method('getBody');
        }

        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response);

        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->atLeastOnce())->method('withHeader')->willReturnSelf();
        $request->method('withBody')->willReturnSelf();

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects($this->once())->method('createRequest')->willReturn($request);

        $streamFactory = null;
        if ($withStreamFactory) {
            $bodyStream = $this->createMock(StreamInterface::class);
            $bodyStream->expects($this->never())->method($this->anything());

            $streamFactory = $this->createMock(StreamFactoryInterface::class);
            $streamFactory->expects($this->once())
                ->method('createStream')
                ->willReturn($bodyStream);
        }

        return new TwitterApiClient(
            $httpClient,
            $requestFactory,
            new TwitterApiConfig(),
            $streamFactory,
        );
    }
}
