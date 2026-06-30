<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit;

use Horde\Service\Twitter\V2\TweetFieldsParams;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TweetFieldsParams::class)]
final class TweetFieldsParamsTest extends TestCase
{
    public function testDefaultsProduceEmptyParams(): void
    {
        $params = new TweetFieldsParams();

        self::assertSame([], $params->toQueryParams());
    }

    public function testTweetFields(): void
    {
        $params = new TweetFieldsParams(tweetFields: ['created_at', 'public_metrics']);
        $query = $params->toQueryParams();

        self::assertSame('created_at,public_metrics', $query['tweet.fields']);
        self::assertArrayNotHasKey('user.fields', $query);
        self::assertArrayNotHasKey('expansions', $query);
    }

    public function testUserFields(): void
    {
        $params = new TweetFieldsParams(userFields: ['profile_image_url']);

        self::assertSame(['user.fields' => 'profile_image_url'], $params->toQueryParams());
    }

    public function testExpansions(): void
    {
        $params = new TweetFieldsParams(expansions: ['author_id', 'referenced_tweets.id']);

        self::assertSame(['expansions' => 'author_id,referenced_tweets.id'], $params->toQueryParams());
    }

    public function testAllFieldsCombined(): void
    {
        $params = new TweetFieldsParams(
            tweetFields: ['created_at'],
            userFields: ['name'],
            expansions: ['author_id'],
        );

        $query = $params->toQueryParams();
        self::assertCount(3, $query);
        self::assertSame('created_at', $query['tweet.fields']);
        self::assertSame('name', $query['user.fields']);
        self::assertSame('author_id', $query['expansions']);
    }

    public function testMediaPollPlaceFieldsEmitted(): void
    {
        $params = new TweetFieldsParams(
            mediaFields: ['url', 'preview_image_url', 'alt_text'],
            pollFields: ['options', 'voting_status'],
            placeFields: ['full_name', 'country_code'],
        );

        $query = $params->toQueryParams();

        self::assertSame('url,preview_image_url,alt_text', $query['media.fields']);
        self::assertSame('options,voting_status', $query['poll.fields']);
        self::assertSame('full_name,country_code', $query['place.fields']);
    }

    public function testEmptyMediaPollPlaceArraysAreOmitted(): void
    {
        $params = new TweetFieldsParams(mediaFields: [], pollFields: [], placeFields: []);

        self::assertSame([], $params->toQueryParams());
    }
}
