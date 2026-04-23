<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V1\Test\Unit;

use Horde\Service\Twitter\V1\CursorParams;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CursorParams::class)]
final class CursorParamsTest extends TestCase
{
    public function testDefaultsProduceEmptyQueryString(): void
    {
        $params = new CursorParams();

        self::assertSame('', $params->toQueryString());
    }

    public function testNonDefaultCursorIncluded(): void
    {
        $params = new CursorParams(cursor: 123456);

        $query = $params->toQueryString();

        self::assertStringContainsString('cursor=123456', $query);
    }

    public function testScreenNameAndSkipStatus(): void
    {
        $params = new CursorParams(screenName: 'alice', skipStatus: true);

        $query = $params->toQueryString();

        self::assertStringContainsString('screen_name=alice', $query);
        self::assertStringContainsString('skip_status=true', $query);
    }
}
