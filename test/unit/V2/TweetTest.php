<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit;

use Horde\Service\Twitter\V2\Tweet;
use Horde\Service\Twitter\V2\TweetAttachments;
use Horde\Service\Twitter\V2\TweetEntities;
use Horde\Service\Twitter\V2\TweetGeo;
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
        self::assertNull($tweet->entities);
        self::assertNull($tweet->attachments);
        self::assertNull($tweet->geo);
        self::assertNull($tweet->contextAnnotations);
        self::assertNull($tweet->replySettings);
        self::assertNull($tweet->possiblySensitive);
        self::assertNull($tweet->withheld);
        self::assertNull($tweet->source);
        self::assertNull($tweet->noteTweet);
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

    public function testEntitiesAttachmentsGeoParsedAsTypedObjects(): void
    {
        $data = (object) [
            'id' => '500',
            'text' => 'See https://example.com #horde',
            'entities' => (object) [
                'urls' => [
                    (object) ['start' => 4, 'end' => 23, 'url' => 'https://t.co/abc'],
                ],
                'hashtags' => [
                    (object) ['start' => 24, 'end' => 30, 'tag' => 'horde'],
                ],
            ],
            'attachments' => (object) ['media_keys' => ['3_1']],
            'geo' => (object) [
                'place_id' => 'sf',
                'coordinates' => (object) [
                    'type' => 'Point',
                    'coordinates' => [-122.4, 37.7],
                ],
            ],
        ];

        $tweet = Tweet::fromApiResponse($data);

        self::assertInstanceOf(TweetEntities::class, $tweet->entities);
        self::assertNotNull($tweet->entities->urls);
        self::assertSame('horde', $tweet->entities->hashtags[0]['tag']);

        self::assertInstanceOf(TweetAttachments::class, $tweet->attachments);
        self::assertSame(['3_1'], $tweet->attachments->mediaKeys);

        self::assertInstanceOf(TweetGeo::class, $tweet->geo);
        self::assertSame('sf', $tweet->geo->placeId);
        self::assertSame([-122.4, 37.7], $tweet->geo->coordinates['coordinates']);
    }

    public function testContextAnnotationsParsedAsDomainEntityRows(): void
    {
        $data = (object) [
            'id' => '1',
            'text' => 'x',
            'context_annotations' => [
                (object) [
                    'domain' => (object) ['id' => '46', 'name' => 'Brand Category'],
                    'entity' => (object) ['id' => '781974596752842752', 'name' => 'Services'],
                ],
                // Garbage rows are skipped.
                'not-an-object',
            ],
        ];

        $tweet = Tweet::fromApiResponse($data);

        self::assertCount(1, $tweet->contextAnnotations);
        self::assertSame('Brand Category', $tweet->contextAnnotations[0]['domain']['name']);
        self::assertSame('Services', $tweet->contextAnnotations[0]['entity']['name']);
    }

    public function testReplySettingsPossiblySensitiveWithheldSourceNoteTweet(): void
    {
        $data = (object) [
            'id' => '1',
            'text' => 'x',
            'reply_settings' => 'following',
            'possibly_sensitive' => true,
            'withheld' => (object) [
                'country_codes' => ['DE', 'FR'],
                'scope' => 'tweet',
            ],
            'source' => 'Twitter for iPhone',
            'note_tweet' => (object) [
                'text' => 'Long form text that exceeds 280 chars…',
                'entities' => (object) ['hashtags' => []],
            ],
        ];

        $tweet = Tweet::fromApiResponse($data);

        self::assertSame('following', $tweet->replySettings);
        self::assertTrue($tweet->possiblySensitive);
        self::assertSame(['DE', 'FR'], $tweet->withheld['country_codes']);
        self::assertSame('Twitter for iPhone', $tweet->source);
        self::assertSame('Long form text that exceeds 280 chars…', $tweet->noteTweet['text']);
        self::assertArrayHasKey('entities', $tweet->noteTweet);
    }
}
