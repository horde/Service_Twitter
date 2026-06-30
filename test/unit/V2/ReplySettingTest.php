<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit;

use Horde\Service\Twitter\V2\ReplySetting;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReplySetting::class)]
final class ReplySettingTest extends TestCase
{
    public function testEnumValuesMatchApiSpec(): void
    {
        self::assertSame('everyone', ReplySetting::Everyone->value);
        self::assertSame('mentionedUsers', ReplySetting::MentionedUsers->value);
        self::assertSame('following', ReplySetting::Following->value);
        self::assertSame('subscribers', ReplySetting::Subscribers->value);
    }

    public function testFromRoundTrip(): void
    {
        self::assertSame(ReplySetting::Following, ReplySetting::from('following'));
    }
}
