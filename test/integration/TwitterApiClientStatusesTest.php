<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V1\Test\Integration;

use Horde\Service\Twitter\V1\Request\DestroyTweetRequestFactory;
use Horde\Service\Twitter\V1\Request\HomeTimelineRequestFactory;
use Horde\Service\Twitter\V1\Request\MentionsTimelineRequestFactory;
use Horde\Service\Twitter\V1\Request\RetweetRequestFactory;
use Horde\Service\Twitter\V1\Request\RetweetsOfMeRequestFactory;
use Horde\Service\Twitter\V1\Request\ShowTweetRequestFactory;
use Horde\Service\Twitter\V1\Request\UpdateStatusRequestFactory;
use Horde\Service\Twitter\V1\Request\UserTimelineRequestFactory;
use Horde\Service\Twitter\V1\TimelineParams;
use Horde\Service\Twitter\V1\Tweet;
use Horde\Service\Twitter\V1\TweetList;
use Horde\Service\Twitter\V1\TwitterApiClient;
use Horde\Service\Twitter\V1\TwitterApiConfig;
use Horde\Service\Twitter\V1\TwitterApiException;
use Horde\Service\Twitter\V1\TwitterUser;
use Horde\Service\Twitter\V1\UpdateStatusParams;
use Horde\Service\Twitter\V1\UserTimelineParams;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(TwitterApiClient::class)]
#[CoversClass(ShowTweetRequestFactory::class)]
#[CoversClass(UpdateStatusRequestFactory::class)]
#[CoversClass(HomeTimelineRequestFactory::class)]
#[CoversClass(UserTimelineRequestFactory::class)]
#[CoversClass(RetweetRequestFactory::class)]
#[CoversClass(DestroyTweetRequestFactory::class)]
#[CoversClass(MentionsTimelineRequestFactory::class)]
#[CoversClass(RetweetsOfMeRequestFactory::class)]
#[CoversClass(Tweet::class)]
#[CoversClass(TweetList::class)]
#[CoversClass(TwitterUser::class)]
final class TwitterApiClientStatusesTest extends TestCase
{
    private function tweetJson(int $id = 1, string $text = 'Hello'): string
    {
        return json_encode([
            'id' => $id,
            'id_str' => (string) $id,
            'text' => $text,
            'user' => ['id' => 42, 'screen_name' => 'testuser'],
            'created_at' => 'Wed Oct 10 20:19:24 +0000 2018',
            'retweet_count' => 0,
            'favorite_count' => 0,
            'retweeted' => false,
            'favorited' => false,
            'source' => 'web',
        ]);
    }

    private function tweetListJson(int $count = 2): string
    {
        $tweets = [];
        for ($i = 1; $i <= $count; $i++) {
            $tweets[] = [
                'id' => $i,
                'id_str' => (string) $i,
                'text' => "Tweet $i",
                'user' => ['id' => 42, 'screen_name' => 'testuser'],
            ];
        }
        return json_encode($tweets);
    }

    public function testShowTweetReturnsTweet(): void
    {
        $client = $this->buildClient(200, $this->tweetJson(12345, 'Found it'));
        $tweet = $client->showTweet(12345);

        self::assertSame(12345, $tweet->id);
        self::assertSame('Found it', $tweet->text);
    }

    public function testUpdateStatusSendsPostAndReturnsTweet(): void
    {
        $client = $this->buildClient(200, $this->tweetJson(1, 'Posted'), withStreamFactory: true);
        $tweet = $client->updateStatus(new UpdateStatusParams(status: 'Posted'));

        self::assertSame('Posted', $tweet->text);
    }

    public function testGetHomeTimelineReturnsTweetList(): void
    {
        $client = $this->buildClient(200, $this->tweetListJson(3));
        $list = $client->getHomeTimeline();

        self::assertCount(3, $list);
    }

    public function testGetUserTimelinePassesParams(): void
    {
        $client = $this->buildClient(200, $this->tweetListJson(1));
        $list = $client->getUserTimeline(new UserTimelineParams(screenName: 'alice'));

        self::assertCount(1, $list);
    }

    public function testRetweetSendsPostWithIdInPath(): void
    {
        $client = $this->buildClient(200, $this->tweetJson(999, 'Retweeted'), withStreamFactory: true);
        $tweet = $client->retweet(999);

        self::assertSame(999, $tweet->id);
    }

    public function testDestroyTweetSendsPostWithIdInPath(): void
    {
        $client = $this->buildClient(200, $this->tweetJson(555, 'Destroyed'), withStreamFactory: true);
        $tweet = $client->destroyTweet(555);

        self::assertSame(555, $tweet->id);
    }

    public function testGetMentionsTimelineReturnsTweetList(): void
    {
        $client = $this->buildClient(200, $this->tweetListJson(2));
        $list = $client->getMentionsTimeline();

        self::assertCount(2, $list);
    }

    public function testGetRetweetsOfMeReturnsTweetList(): void
    {
        $client = $this->buildClient(200, $this->tweetListJson(1));
        $list = $client->getRetweetsOfMe();

        self::assertCount(1, $list);
    }

    public function testUpdateStatusThrowsWithoutStreamFactory(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->expects($this->never())->method('sendRequest');

        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->never())->method($this->anything());

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects($this->never())->method('createRequest');

        $client = new TwitterApiClient(
            $httpClient,
            $requestFactory,
            new TwitterApiConfig(),
        );

        $this->expectException(TwitterApiException::class);
        $this->expectExceptionMessage('StreamFactoryInterface is required');

        $client->updateStatus(new UpdateStatusParams(status: 'Fail'));
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
