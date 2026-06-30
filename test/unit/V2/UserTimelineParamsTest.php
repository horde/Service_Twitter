<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit;

use DateTimeImmutable;
use DateTimeZone;
use Horde\Service\Twitter\V2\UserTimelineParams;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserTimelineParams::class)]
final class UserTimelineParamsTest extends TestCase
{
    public function testDefaultsProduceEmptyParams(): void
    {
        $params = new UserTimelineParams();

        self::assertSame([], $params->toQueryParams());
    }

    public function testMaxResults(): void
    {
        $params = new UserTimelineParams(maxResults: 50);

        self::assertSame(['max_results' => '50'], $params->toQueryParams());
    }

    public function testPaginationToken(): void
    {
        $params = new UserTimelineParams(paginationToken: 'abc123');

        self::assertSame(['pagination_token' => 'abc123'], $params->toQueryParams());
    }

    public function testSinceIdAndUntilId(): void
    {
        $params = new UserTimelineParams(sinceId: '100', untilId: '200');
        $query = $params->toQueryParams();

        self::assertSame('100', $query['since_id']);
        self::assertSame('200', $query['until_id']);
    }

    public function testAllFields(): void
    {
        $params = new UserTimelineParams(
            maxResults: 10,
            paginationToken: 'tok',
            sinceId: '50',
            untilId: '100',
        );

        self::assertCount(4, $params->toQueryParams());
    }

    public function testExcludeJoinedWithComma(): void
    {
        $params = new UserTimelineParams(exclude: ['retweets', 'replies']);

        self::assertSame(['exclude' => 'retweets,replies'], $params->toQueryParams());
    }

    public function testEmptyExcludeArrayOmittedFromQuery(): void
    {
        $params = new UserTimelineParams(exclude: []);

        self::assertArrayNotHasKey('exclude', $params->toQueryParams());
    }

    public function testStartAndEndTimeNormalisedToIsoUtc(): void
    {
        // Provide a non-UTC zone to confirm the params class converts.
        $start = new DateTimeImmutable('2026-04-01 08:30:00', new DateTimeZone('America/New_York'));
        $end = new DateTimeImmutable('2026-04-02 08:30:00', new DateTimeZone('UTC'));

        $query = (new UserTimelineParams(startTime: $start, endTime: $end))->toQueryParams();

        self::assertSame('2026-04-01T12:30:00Z', $query['start_time']);
        self::assertSame('2026-04-02T08:30:00Z', $query['end_time']);
    }
}
