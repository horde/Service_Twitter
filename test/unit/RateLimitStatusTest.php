<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V1\Test\Unit;

use Horde\Service\Twitter\V1\RateLimitResource;
use Horde\Service\Twitter\V1\RateLimitStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RateLimitStatus::class)]
#[CoversClass(RateLimitResource::class)]
final class RateLimitStatusTest extends TestCase
{
    public function testFromApiResponseParsesNestedResources(): void
    {
        $data = (object) [
            'rate_limit_context' => (object) ['access_token' => 'abc123'],
            'resources' => (object) [
                'statuses' => (object) [
                    '/statuses/home_timeline' => (object) [
                        'limit' => 15,
                        'remaining' => 10,
                        'reset' => 1700000000,
                    ],
                    '/statuses/mentions_timeline' => (object) [
                        'limit' => 75,
                        'remaining' => 75,
                        'reset' => 1700000000,
                    ],
                ],
                'friends' => (object) [
                    '/friends/list' => (object) [
                        'limit' => 15,
                        'remaining' => 0,
                        'reset' => 1700000900,
                    ],
                ],
            ],
        ];

        $status = RateLimitStatus::fromApiResponse($data);

        self::assertSame('abc123', $status->context);
        self::assertCount(3, $status->resources);
        self::assertArrayHasKey('/statuses/home_timeline', $status->resources);
        self::assertArrayHasKey('/statuses/mentions_timeline', $status->resources);
        self::assertArrayHasKey('/friends/list', $status->resources);

        $homeTimeline = $status->resources['/statuses/home_timeline'];
        self::assertSame(15, $homeTimeline->limit);
        self::assertSame(10, $homeTimeline->remaining);

        $friends = $status->resources['/friends/list'];
        self::assertTrue($friends->isExhausted());
    }

    public function testFromApiResponseWithEmptyResources(): void
    {
        $data = (object) [
            'rate_limit_context' => (object) ['access_token' => 'xyz'],
            'resources' => (object) [],
        ];

        $status = RateLimitStatus::fromApiResponse($data);

        self::assertSame('xyz', $status->context);
        self::assertCount(0, $status->resources);
    }
}
