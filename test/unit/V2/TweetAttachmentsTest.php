<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit;

use Horde\Service\Twitter\V2\TweetAttachments;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TweetAttachments::class)]
final class TweetAttachmentsTest extends TestCase
{
    public function testParsesMediaKeysAndPollIds(): void
    {
        $data = (object) [
            'media_keys' => ['7_1234', '13_5678'],
            'poll_ids' => ['9999'],
        ];

        $attachments = TweetAttachments::fromApiResponse($data);

        self::assertSame(['7_1234', '13_5678'], $attachments->mediaKeys);
        self::assertSame(['9999'], $attachments->pollIds);
    }

    public function testMissingFieldsStayNull(): void
    {
        $attachments = TweetAttachments::fromApiResponse((object) []);

        self::assertNull($attachments->mediaKeys);
        self::assertNull($attachments->pollIds);
    }

    public function testStringValuesCoerced(): void
    {
        $data = (object) ['media_keys' => [123, 456]];

        $attachments = TweetAttachments::fromApiResponse($data);

        self::assertSame(['123', '456'], $attachments->mediaKeys);
    }
}
