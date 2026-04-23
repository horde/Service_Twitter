<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V1\Test\Unit;

use Horde\Service\Twitter\V1\RateLimitResource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RateLimitResource::class)]
final class RateLimitResourceTest extends TestCase
{
    public function testFromApiResponse(): void
    {
        $data = (object) [
            'limit' => 15,
            'remaining' => 10,
            'reset' => 1700000000,
        ];

        $resource = RateLimitResource::fromApiResponse('/statuses/home_timeline', $data);

        self::assertSame('/statuses/home_timeline', $resource->endpoint);
        self::assertSame(15, $resource->limit);
        self::assertSame(10, $resource->remaining);
        self::assertSame(1700000000, $resource->reset);
    }

    public function testIsExhaustedReturnsTrueWhenRemainingZero(): void
    {
        $resource = new RateLimitResource('/test', 15, 0, time() + 900);

        self::assertTrue($resource->isExhausted());
    }

    public function testIsExhaustedReturnsFalseWhenRemaining(): void
    {
        $resource = new RateLimitResource('/test', 15, 5, time() + 900);

        self::assertFalse($resource->isExhausted());
    }

    public function testGetSecondsUntilResetReturnsPositiveDelta(): void
    {
        $futureReset = time() + 300;
        $resource = new RateLimitResource('/test', 15, 0, $futureReset);

        $seconds = $resource->getSecondsUntilReset();

        self::assertGreaterThan(0, $seconds);
        self::assertLessThanOrEqual(300, $seconds);
    }

    public function testGetSecondsUntilResetReturnsZeroWhenPast(): void
    {
        $resource = new RateLimitResource('/test', 15, 0, time() - 100);

        self::assertSame(0, $resource->getSecondsUntilReset());
    }
}
