<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit;

use Horde\Service\Twitter\V2\TweetGeo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TweetGeo::class)]
final class TweetGeoTest extends TestCase
{
    public function testPlaceIdOnly(): void
    {
        $geo = TweetGeo::fromApiResponse((object) ['place_id' => '01a9a39529b27f36']);

        self::assertSame('01a9a39529b27f36', $geo->placeId);
        self::assertNull($geo->coordinates);
    }

    public function testCoordinatesObject(): void
    {
        $data = (object) [
            'place_id' => 'abc',
            'coordinates' => (object) [
                'type' => 'Point',
                'coordinates' => [-122.4194, 37.7749],
            ],
        ];

        $geo = TweetGeo::fromApiResponse($data);

        self::assertSame('abc', $geo->placeId);
        self::assertNotNull($geo->coordinates);
        self::assertSame('Point', $geo->coordinates['type']);
        self::assertSame([-122.4194, 37.7749], $geo->coordinates['coordinates']);
    }

    public function testEmptyObjectYieldsNullFields(): void
    {
        $geo = TweetGeo::fromApiResponse((object) []);

        self::assertNull($geo->placeId);
        self::assertNull($geo->coordinates);
    }
}
