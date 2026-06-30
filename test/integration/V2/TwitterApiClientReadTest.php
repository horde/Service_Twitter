<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Integration;

use Horde\Service\Twitter\V2\Tweet;
use Horde\Service\Twitter\V2\TwitterApiClient;
use Horde\Service\Twitter\V2\TwitterApiConfig;
use Horde\Service\Twitter\V2\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(TwitterApiClient::class)]
#[CoversClass(User::class)]
#[CoversClass(Tweet::class)]
final class TwitterApiClientReadTest extends TestCase
{
    public function testGetMeReturnsUser(): void
    {
        $json = json_encode([
            'data' => [
                'id' => '12345',
                'name' => 'Alice',
                'username' => 'alice',
                'description' => 'Developer',
            ],
        ]);

        $client = $this->buildClient(200, $json);
        $user = $client->getMe();

        self::assertSame('12345', $user->id);
        self::assertSame('alice', $user->username);
        self::assertSame('Alice', $user->name);
    }

    public function testGetTweetReturnsTweet(): void
    {
        $json = json_encode([
            'data' => [
                'id' => '999',
                'text' => 'Hello from V2',
                'author_id' => '12345',
                'created_at' => '2026-04-24T12:00:00.000Z',
            ],
        ]);

        $client = $this->buildClient(200, $json);
        $tweet = $client->getTweet('999');

        self::assertSame('999', $tweet->id);
        self::assertSame('Hello from V2', $tweet->text);
        self::assertSame('12345', $tweet->authorId);
    }

    public function testGetUserTimelineReturnsPaginatedResponse(): void
    {
        $json = json_encode([
            'data' => [
                ['id' => '1', 'text' => 'First'],
                ['id' => '2', 'text' => 'Second'],
            ],
            'meta' => [
                'next_token' => 'abc123',
                'result_count' => 2,
            ],
        ]);

        $client = $this->buildClient(200, $json);
        $response = $client->getUserTimeline('42');

        self::assertCount(2, $response->data);
        self::assertInstanceOf(Tweet::class, $response->data[0]);
        self::assertSame('First', $response->data[0]->text);
        self::assertTrue($response->meta->hasNextPage());
        self::assertSame('abc123', $response->meta->nextToken);
    }

    public function testGetBookmarksReturnsPaginatedResponse(): void
    {
        $json = json_encode([
            'data' => [
                ['id' => '10', 'text' => 'Bookmarked tweet'],
            ],
            'meta' => [
                'result_count' => 1,
            ],
        ]);

        $client = $this->buildClient(200, $json);
        $response = $client->getBookmarks('42');

        self::assertCount(1, $response->data);
        self::assertSame('Bookmarked tweet', $response->data[0]->text);
        self::assertFalse($response->meta->hasNextPage());
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
