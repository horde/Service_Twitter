<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V1\Test\Integration;

use Horde\Service\Twitter\V1\Request\CreateFavoriteRequestFactory;
use Horde\Service\Twitter\V1\Request\DestroyFavoriteRequestFactory;
use Horde\Service\Twitter\V1\Request\ListFavoritesRequestFactory;
use Horde\Service\Twitter\V1\Tweet;
use Horde\Service\Twitter\V1\TweetList;
use Horde\Service\Twitter\V1\TwitterApiClient;
use Horde\Service\Twitter\V1\TwitterApiConfig;
use Horde\Service\Twitter\V1\TwitterUser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(TwitterApiClient::class)]
#[CoversClass(ListFavoritesRequestFactory::class)]
#[CoversClass(CreateFavoriteRequestFactory::class)]
#[CoversClass(DestroyFavoriteRequestFactory::class)]
#[CoversClass(Tweet::class)]
#[CoversClass(TweetList::class)]
#[CoversClass(TwitterUser::class)]
final class TwitterApiClientFavoritesTest extends TestCase
{
    public function testGetFavoritesReturnsTweetList(): void
    {
        $json = json_encode([
            ['id' => 1, 'text' => 'Fav 1', 'user' => ['id' => 1, 'screen_name' => 'a']],
            ['id' => 2, 'text' => 'Fav 2', 'user' => ['id' => 2, 'screen_name' => 'b']],
        ]);

        $client = $this->buildClient(200, $json);
        $list = $client->getFavorites();

        self::assertCount(2, $list);
    }

    public function testCreateFavoriteSendsPost(): void
    {
        $json = json_encode([
            'id' => 42,
            'text' => 'Liked',
            'user' => ['id' => 1, 'screen_name' => 'test'],
        ]);

        $client = $this->buildClient(200, $json, withStreamFactory: true);
        $tweet = $client->createFavorite(42);

        self::assertSame(42, $tweet->id);
    }

    public function testDestroyFavoriteSendsPost(): void
    {
        $json = json_encode([
            'id' => 42,
            'text' => 'Unliked',
            'user' => ['id' => 1, 'screen_name' => 'test'],
        ]);

        $client = $this->buildClient(200, $json, withStreamFactory: true);
        $tweet = $client->destroyFavorite(42);

        self::assertSame(42, $tweet->id);
    }

    private function buildClient(int $statusCode, string $body, bool $withStreamFactory = false): TwitterApiClient
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->atLeastOnce())->method('__toString')->willReturn($body);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('getStatusCode')->willReturn($statusCode);
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

        $streamFactory = null;
        if ($withStreamFactory) {
            $bodyStream = $this->createMock(StreamInterface::class);
            $bodyStream->expects($this->never())->method($this->anything());

            $streamFactory = $this->createMock(StreamFactoryInterface::class);
            $streamFactory->expects($this->once())->method('createStream')->willReturn($bodyStream);
        }

        return new TwitterApiClient(
            $httpClient,
            $requestFactory,
            new TwitterApiConfig(),
            $streamFactory,
        );
    }
}
