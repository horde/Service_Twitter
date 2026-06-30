<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit;

use Horde\Service\Twitter\V2\PaginationMeta;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PaginationMeta::class)]
final class PaginationMetaTest extends TestCase
{
    public function testFromApiResponseWithAllFields(): void
    {
        $data = (object) [
            'next_token' => 'abc123',
            'previous_token' => 'xyz789',
            'result_count' => 10,
        ];

        $meta = PaginationMeta::fromApiResponse($data);

        self::assertSame('abc123', $meta->nextToken);
        self::assertSame('xyz789', $meta->previousToken);
        self::assertSame(10, $meta->resultCount);
        self::assertTrue($meta->hasNextPage());
    }

    public function testFromApiResponseWithNoNextToken(): void
    {
        $data = (object) [
            'result_count' => 5,
        ];

        $meta = PaginationMeta::fromApiResponse($data);

        self::assertNull($meta->nextToken);
        self::assertNull($meta->previousToken);
        self::assertSame(5, $meta->resultCount);
        self::assertFalse($meta->hasNextPage());
    }

    public function testFromEmptyObject(): void
    {
        $meta = PaginationMeta::fromApiResponse((object) []);

        self::assertNull($meta->nextToken);
        self::assertNull($meta->previousToken);
        self::assertSame(0, $meta->resultCount);
        self::assertFalse($meta->hasNextPage());
    }
}
