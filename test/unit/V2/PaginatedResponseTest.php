<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit;

use Horde\Service\Twitter\V2\PaginatedResponse;
use Horde\Service\Twitter\V2\PaginationMeta;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PaginatedResponse::class)]
final class PaginatedResponseTest extends TestCase
{
    public function testConstructWithDataAndMeta(): void
    {
        $meta = new PaginationMeta(nextToken: 'abc', resultCount: 2);
        $response = new PaginatedResponse(['item1', 'item2'], $meta);

        self::assertSame(['item1', 'item2'], $response->data);
        self::assertSame($meta, $response->meta);
        self::assertSame('abc', $response->meta->nextToken);
    }

    public function testEmptyDataArray(): void
    {
        $meta = new PaginationMeta(resultCount: 0);
        $response = new PaginatedResponse([], $meta);

        self::assertCount(0, $response->data);
        self::assertSame(0, $response->meta->resultCount);
    }
}
