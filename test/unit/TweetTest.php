<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V1\Test\Unit;

use Horde\Service\Twitter\V1\Tweet;
use Horde\Service\Twitter\V1\TwitterUser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Tweet::class)]
#[CoversClass(TwitterUser::class)]
final class TweetTest extends TestCase
{
    public function testFromApiResponseWithCompleteData(): void
    {
        $data = (object) [
            'id' => 123456789,
            'id_str' => '123456789',
            'text' => 'Hello world',
            'user' => (object) [
                'id' => 42,
                'name' => 'Test User',
                'screen_name' => 'testuser',
                'description' => 'A test user',
                'profile_image_url_https' => 'https://example.com/img.jpg',
                'followers_count' => 100,
                'friends_count' => 50,
                'statuses_count' => 200,
                'created_at' => 'Mon Jan 01 00:00:00 +0000 2020',
                'verified' => true,
                'protected' => false,
            ],
            'created_at' => 'Wed Oct 10 20:19:24 +0000 2018',
            'in_reply_to_status_id' => 111,
            'in_reply_to_user_id' => 222,
            'in_reply_to_screen_name' => 'otheruser',
            'retweet_count' => 5,
            'favorite_count' => 10,
            'retweeted' => false,
            'favorited' => true,
            'source' => '<a href="https://mobile.twitter.com">Twitter Web App</a>',
        ];

        $tweet = Tweet::fromApiResponse($data);

        self::assertSame(123456789, $tweet->id);
        self::assertSame('123456789', $tweet->idStr);
        self::assertSame('Hello world', $tweet->text);
        self::assertSame(42, $tweet->user->id);
        self::assertSame('testuser', $tweet->user->screenName);
        self::assertSame('Wed Oct 10 20:19:24 +0000 2018', $tweet->createdAt);
        self::assertSame(111, $tweet->inReplyToStatusId);
        self::assertSame(222, $tweet->inReplyToUserId);
        self::assertSame('otheruser', $tweet->inReplyToScreenName);
        self::assertSame(5, $tweet->retweetCount);
        self::assertSame(10, $tweet->favoriteCount);
        self::assertFalse($tweet->retweeted);
        self::assertTrue($tweet->favorited);
        self::assertNull($tweet->retweetedStatus);
        self::assertSame('<a href="https://mobile.twitter.com">Twitter Web App</a>', $tweet->source);
    }

    public function testFromApiResponsePrefersFullText(): void
    {
        $data = (object) [
            'text' => 'Truncated...',
            'full_text' => 'Full tweet text that is longer than truncated version',
        ];

        $tweet = Tweet::fromApiResponse($data);

        self::assertSame('Full tweet text that is longer than truncated version', $tweet->text);
    }

    public function testFromApiResponseWithMinimalData(): void
    {
        $tweet = Tweet::fromApiResponse((object) []);

        self::assertSame(0, $tweet->id);
        self::assertSame('0', $tweet->idStr);
        self::assertSame('', $tweet->text);
        self::assertSame('', $tweet->createdAt);
        self::assertNull($tweet->inReplyToStatusId);
        self::assertNull($tweet->inReplyToUserId);
        self::assertNull($tweet->inReplyToScreenName);
        self::assertSame(0, $tweet->retweetCount);
        self::assertSame(0, $tweet->favoriteCount);
        self::assertFalse($tweet->retweeted);
        self::assertFalse($tweet->favorited);
        self::assertNull($tweet->retweetedStatus);
        self::assertSame('', $tweet->source);
    }

    public function testFromApiResponseWithRetweetedStatus(): void
    {
        $data = (object) [
            'id' => 1,
            'id_str' => '1',
            'text' => 'RT @original: Original tweet',
            'retweeted_status' => (object) [
                'id' => 2,
                'id_str' => '2',
                'text' => 'Original tweet',
                'user' => (object) ['screen_name' => 'original'],
            ],
        ];

        $tweet = Tweet::fromApiResponse($data);

        self::assertNotNull($tweet->retweetedStatus);
        self::assertSame(2, $tweet->retweetedStatus->id);
        self::assertSame('Original tweet', $tweet->retweetedStatus->text);
        self::assertSame('original', $tweet->retweetedStatus->user->screenName);
    }

    public function testFromApiResponseWithNullReplyFields(): void
    {
        $data = (object) [
            'id' => 1,
            'text' => 'Not a reply',
        ];

        $tweet = Tweet::fromApiResponse($data);

        self::assertNull($tweet->inReplyToStatusId);
        self::assertNull($tweet->inReplyToUserId);
        self::assertNull($tweet->inReplyToScreenName);
    }

    public function testToStringReturnsText(): void
    {
        $data = (object) [
            'text' => 'Hello world',
        ];

        $tweet = Tweet::fromApiResponse($data);

        self::assertSame('Hello world', (string) $tweet);
    }
}
