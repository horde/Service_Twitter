<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit;

use Horde\Service\Twitter\V2\CreateTweetParams;
use Horde\Service\Twitter\V2\ReplySetting;
use InvalidArgumentException;
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

    public function testMediaOnlyTweet(): void
    {
        $params = new CreateTweetParams(
            text: '',
            mediaIds: ['3_1', '3_2'],
            taggedUserIds: ['42', '99'],
        );
        $array = $params->toArray();

        self::assertArrayNotHasKey('text', $array);
        self::assertSame(['3_1', '3_2'], $array['media']['media_ids']);
        self::assertSame(['42', '99'], $array['media']['tagged_user_ids']);
    }

    public function testPollTweet(): void
    {
        $params = new CreateTweetParams(
            text: 'Pick one',
            pollOptions: ['Yes', 'No', 'Maybe'],
            pollDurationMinutes: 1440,
        );
        $array = $params->toArray();

        self::assertSame(['Yes', 'No', 'Maybe'], $array['poll']['options']);
        self::assertSame(1440, $array['poll']['duration_minutes']);
    }

    public function testReplySettingEmittedAsApiValue(): void
    {
        $params = new CreateTweetParams(
            text: 'Limited audience',
            replySettings: ReplySetting::Following,
        );

        self::assertSame('following', $params->toArray()['reply_settings']);
    }

    public function testPlaceIdEmittedUnderGeo(): void
    {
        $params = new CreateTweetParams(text: 'From here', placeId: '01a9a39529b27f36');

        self::assertSame(['place_id' => '01a9a39529b27f36'], $params->toArray()['geo']);
    }

    public function testSuperFollowersAndDmDeepLink(): void
    {
        $params = new CreateTweetParams(
            text: 'Premium content',
            forSuperFollowersOnly: true,
            directMessageDeepLink: 'https://twitter.com/messages/compose?recipient_id=42',
        );
        $array = $params->toArray();

        self::assertTrue($array['for_super_followers_only']);
        self::assertSame(
            'https://twitter.com/messages/compose?recipient_id=42',
            $array['direct_message_deep_link'],
        );
    }

    public function testExcludeReplyUserIdsMergedIntoReplyObject(): void
    {
        $params = new CreateTweetParams(
            text: 'Replying without notifying',
            inReplyToTweetId: '123',
            excludeReplyUserIds: ['999'],
        );

        self::assertSame(
            ['in_reply_to_tweet_id' => '123', 'exclude_reply_user_ids' => ['999']],
            $params->toArray()['reply'],
        );
    }

    public function testMediaAndPollAreMutuallyExclusive(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CreateTweetParams(
            text: 'Cannot have both',
            mediaIds: ['3_1'],
            pollOptions: ['a', 'b'],
            pollDurationMinutes: 60,
        );
    }

    public function testPollOptionsRequiresDuration(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CreateTweetParams(text: 'Vote', pollOptions: ['a', 'b']);
    }

    public function testPollDurationWithoutOptionsIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CreateTweetParams(text: 'x', pollDurationMinutes: 60);
    }

    public function testEmptyTweetWithoutMediaOrPollIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CreateTweetParams(text: '');
    }
}
