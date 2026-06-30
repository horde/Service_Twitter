<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit;

use Horde\Service\Twitter\V2\Tweet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Tweet::class)]
final class TweetTest extends TestCase
{
    public function testFromApiResponseWithCompleteData(): void
    {
        $data = (object) [
            'id' => '123456789',
            'text' => 'Hello world',
            'author_id' => '999',
            'conversation_id' => '123456789',
            'created_at' => '2026-04-24T12:00:00.000Z',
            'lang' => 'en',
            'in_reply_to_user_id' => '888',
            'referenced_tweets' => [
                (object) ['type' => 'retweeted', 'id' => '111'],
            ],
            'public_metrics' => (object) [
                'retweet_count' => 5,
                'reply_count' => 2,
                'like_count' => 10,
                'quote_count' => 1,
            ],
            'edit_history_tweet_ids' => ['123456789'],
        ];

        $tweet = Tweet::fromApiResponse($data);

        self::assertSame('123456789', $tweet->id);
        self::assertSame('Hello world', $tweet->text);
        self::assertSame('999', $tweet->authorId);
        self::assertSame('123456789', $tweet->conversationId);
        self::assertSame('2026-04-24T12:00:00.000Z', $tweet->createdAt);
        self::assertSame('en', $tweet->lang);
        self::assertSame('888', $tweet->inReplyToUserId);
        self::assertCount(1, $tweet->referencedTweets);
        self::assertSame('retweeted', $tweet->referencedTweets[0]['type']);
        self::assertSame(10, $tweet->publicMetrics['like_count']);
        self::assertSame(['123456789'], $tweet->editHistoryTweetIds);
    }

    public function testFromApiResponseWithMinimalData(): void
    {
        $data = (object) [
            'id' => '42',
            'text' => 'Minimal',
        ];

        $tweet = Tweet::fromApiResponse($data);

        self::assertSame('42', $tweet->id);
        self::assertSame('Minimal', $tweet->text);
        self::assertNull($tweet->authorId);
        self::assertNull($tweet->conversationId);
        self::assertNull($tweet->createdAt);
        self::assertNull($tweet->lang);
        self::assertNull($tweet->inReplyToUserId);
        self::assertNull($tweet->referencedTweets);
        self::assertNull($tweet->publicMetrics);
        self::assertNull($tweet->editHistoryTweetIds);
    }

    public function testToStringReturnsText(): void
    {
        $tweet = Tweet::fromApiResponse((object) ['id' => '1', 'text' => 'Hello']);

        self::assertSame('Hello', (string) $tweet);
    }

    public function testReferencedTweetsIgnoredWhenNotArray(): void
    {
        $data = (object) ['id' => '1', 'text' => 'x', 'referenced_tweets' => 'not-an-array'];

        self::assertNull(Tweet::fromApiResponse($data)->referencedTweets);
    }

    public function testPublicMetricsIgnoredWhenNotObject(): void
    {
        $data = (object) ['id' => '1', 'text' => 'x', 'public_metrics' => 'not-an-object'];

        self::assertNull(Tweet::fromApiResponse($data)->publicMetrics);
    }

    public function testEditHistoryIgnoredWhenNotArray(): void
    {
        $data = (object) ['id' => '1', 'text' => 'x', 'edit_history_tweet_ids' => 'not-an-array'];

        self::assertNull(Tweet::fromApiResponse($data)->editHistoryTweetIds);
    }
}
