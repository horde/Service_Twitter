<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V1\Test\Unit;

use Horde\Service\Twitter\V1\UserTimelineParams;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserTimelineParams::class)]
final class UserTimelineParamsTest extends TestCase
{
    public function testScreenNamePrepended(): void
    {
        $params = new UserTimelineParams(screenName: 'testuser');

        $query = $params->toQueryString();

        self::assertStringContainsString('screen_name=testuser', $query);
    }

    public function testUserIdPrepended(): void
    {
        $params = new UserTimelineParams(userId: 12345);

        $query = $params->toQueryString();

        self::assertStringContainsString('user_id=12345', $query);
    }

    public function testCombinesWithParentParams(): void
    {
        $params = new UserTimelineParams(
            screenName: 'testuser',
            sinceId: 100,
            count: 5,
        );

        $query = $params->toQueryString();

        self::assertStringContainsString('screen_name=testuser', $query);
        self::assertStringContainsString('since_id=100', $query);
        self::assertStringContainsString('count=5', $query);
    }

    public function testDefaultsProduceEmptyQueryString(): void
    {
        $params = new UserTimelineParams();

        self::assertSame('', $params->toQueryString());
    }
}
