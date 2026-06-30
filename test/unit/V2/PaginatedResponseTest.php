<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit;

use Horde\Service\Twitter\V2\Includes;
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
        self::assertNull($response->includes);
    }

    public function testEmptyDataArray(): void
    {
        $meta = new PaginationMeta(resultCount: 0);
        $response = new PaginatedResponse([], $meta);

        self::assertCount(0, $response->data);
        self::assertSame(0, $response->meta->resultCount);
    }

    public function testIteratorYieldsFirstPageWhenNoNextToken(): void
    {
        $meta = new PaginationMeta(nextToken: null, resultCount: 2);
        $response = new PaginatedResponse(['a', 'b'], $meta);

        self::assertSame(['a', 'b'], iterator_to_array($response->iterator(), preserve_keys: false));
    }

    public function testIteratorWalksMultiplePagesViaFetcher(): void
    {
        $page2 = new PaginatedResponse(
            ['c', 'd'],
            new PaginationMeta(nextToken: 'tok2', resultCount: 2),
        );
        $page3 = new PaginatedResponse(
            ['e'],
            new PaginationMeta(nextToken: null, resultCount: 1),
        );
        $tokensSeen = [];
        $pageQueue = [$page2, $page3];
        $fetcher = static function (string $token) use (&$tokensSeen, &$pageQueue): PaginatedResponse {
            $tokensSeen[] = $token;
            return array_shift($pageQueue);
        };

        $page1 = new PaginatedResponse(
            ['a', 'b'],
            new PaginationMeta(nextToken: 'tok1', resultCount: 2),
            null,
            $fetcher,
        );

        self::assertSame(
            ['a', 'b', 'c', 'd', 'e'],
            iterator_to_array($page1->iterator(), preserve_keys: false),
        );
        self::assertSame(['tok1', 'tok2'], $tokensSeen);
    }

    public function testIteratorStopsWhenNoFetcherProvidedEvenIfNextTokenSet(): void
    {
        // Defensive: a fetcher-less response with nextToken just yields the
        // current page rather than blowing up.
        $response = new PaginatedResponse(
            ['only'],
            new PaginationMeta(nextToken: 'unused', resultCount: 1),
        );

        self::assertSame(['only'], iterator_to_array($response->iterator(), preserve_keys: false));
    }

    public function testIncludesPassThrough(): void
    {
        $includes = new Includes();
        $response = new PaginatedResponse(
            [],
            new PaginationMeta(),
            $includes,
        );

        self::assertSame($includes, $response->includes);
    }
}
