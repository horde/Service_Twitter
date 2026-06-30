<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit;

use Horde\Service\Twitter\V2\CreateTweetParams;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CreateTweetParams::class)]
final class CreateTweetParamsTest extends TestCase
{
    public function testTextOnly(): void
    {
        $params = new CreateTweetParams(text: 'Hello world');

        self::assertSame(['text' => 'Hello world'], $params->toArray());
    }

    public function testWithQuoteTweet(): void
    {
        $params = new CreateTweetParams(text: 'Quoting', quoteTweetId: '999');
        $array = $params->toArray();

        self::assertSame('Quoting', $array['text']);
        self::assertSame('999', $array['quote_tweet_id']);
        self::assertArrayNotHasKey('reply', $array);
    }

    public function testWithReply(): void
    {
        $params = new CreateTweetParams(text: 'Replying', inReplyToTweetId: '888');
        $array = $params->toArray();

        self::assertSame('Replying', $array['text']);
        self::assertSame(['in_reply_to_tweet_id' => '888'], $array['reply']);
        self::assertArrayNotHasKey('quote_tweet_id', $array);
    }

    public function testWithAllFields(): void
    {
        $params = new CreateTweetParams(
            text: 'All fields',
            quoteTweetId: '111',
            inReplyToTweetId: '222',
        );
        $array = $params->toArray();

        self::assertSame('All fields', $array['text']);
        self::assertSame('111', $array['quote_tweet_id']);
        self::assertSame(['in_reply_to_tweet_id' => '222'], $array['reply']);
    }
}
