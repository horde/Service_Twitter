<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V1\Test\Unit;

use Horde\Service\Twitter\V1\TimelineParams;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TimelineParams::class)]
final class TimelineParamsTest extends TestCase
{
    public function testDefaultsProduceEmptyQueryString(): void
    {
        $params = new TimelineParams();

        self::assertSame('', $params->toQueryString());
    }

    public function testSinceIdIncluded(): void
    {
        $params = new TimelineParams(sinceId: 12345);

        $query = $params->toQueryString();

        self::assertStringContainsString('since_id=12345', $query);
    }

    public function testNonDefaultCountIncluded(): void
    {
        $params = new TimelineParams(count: 50);

        $query = $params->toQueryString();

        self::assertStringContainsString('count=50', $query);
    }

    public function testTrimUserAndExcludeRepliesSerialized(): void
    {
        $params = new TimelineParams(trimUser: true, excludeReplies: true);

        $query = $params->toQueryString();

        self::assertStringContainsString('trim_user=true', $query);
        self::assertStringContainsString('exclude_replies=true', $query);
    }

    public function testIncludeEntitiesFalseExplicit(): void
    {
        $params = new TimelineParams(includeEntities: false);

        $query = $params->toQueryString();

        self::assertStringContainsString('include_entities=false', $query);
    }
}
