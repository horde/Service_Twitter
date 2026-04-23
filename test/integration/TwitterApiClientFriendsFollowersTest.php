<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V1\Test\Integration;

use Horde\Service\Twitter\V1\CursoredUserList;
use Horde\Service\Twitter\V1\Request\FollowersListRequestFactory;
use Horde\Service\Twitter\V1\Request\FriendsListRequestFactory;
use Horde\Service\Twitter\V1\TwitterApiClient;
use Horde\Service\Twitter\V1\TwitterApiConfig;
use Horde\Service\Twitter\V1\TwitterUser;
use Horde\Service\Twitter\V1\TwitterUserList;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(TwitterApiClient::class)]
#[CoversClass(FriendsListRequestFactory::class)]
#[CoversClass(FollowersListRequestFactory::class)]
#[CoversClass(CursoredUserList::class)]
#[CoversClass(TwitterUserList::class)]
#[CoversClass(TwitterUser::class)]
final class TwitterApiClientFriendsFollowersTest extends TestCase
{
    public function testGetFriendsReturnsCursoredUserList(): void
    {
        $json = json_encode([
            'users' => [
                ['id' => 1, 'screen_name' => 'alice'],
                ['id' => 2, 'screen_name' => 'bob'],
            ],
            'next_cursor' => 12345,
            'previous_cursor' => 0,
        ]);

        $client = $this->buildClient(200, $json);
        $result = $client->getFriends();

        self::assertCount(2, $result->users);
        self::assertTrue($result->hasMore());
        self::assertSame(12345, $result->nextCursor);
    }

    public function testGetFollowersReturnsCursoredUserList(): void
    {
        $json = json_encode([
            'users' => [
                ['id' => 3, 'screen_name' => 'charlie'],
            ],
            'next_cursor' => 0,
            'previous_cursor' => 0,
        ]);

        $client = $this->buildClient(200, $json);
        $result = $client->getFollowers();

        self::assertCount(1, $result->users);
        self::assertFalse($result->hasMore());
    }

    private function buildClient(int $statusCode, string $body): TwitterApiClient
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

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects($this->once())->method('createRequest')->willReturn($request);

        return new TwitterApiClient(
            $httpClient,
            $requestFactory,
            new TwitterApiConfig(),
        );
    }
}
