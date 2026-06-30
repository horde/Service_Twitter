<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit;

use Horde\Service\Twitter\V2\Place;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Place::class)]
final class PlaceTest extends TestCase
{
    public function testFullPlace(): void
    {
        $data = (object) [
            'id' => '01a9a39529b27f36',
            'name' => 'San Francisco',
            'full_name' => 'San Francisco, CA',
            'country' => 'United States',
            'country_code' => 'US',
            'place_type' => 'city',
            'geo' => (object) [
                'type' => 'Feature',
                'bbox' => [-122.514, 37.708, -122.357, 37.833],
            ],
        ];

        $place = Place::fromApiResponse($data);

        self::assertSame('01a9a39529b27f36', $place->id);
        self::assertSame('San Francisco', $place->name);
        self::assertSame('San Francisco, CA', $place->fullName);
        self::assertSame('US', $place->countryCode);
        self::assertSame('city', $place->placeType);
        self::assertNotNull($place->geo);
        self::assertSame('Feature', $place->geo['type']);
    }

    public function testMinimalPlace(): void
    {
        $place = Place::fromApiResponse((object) ['id' => 'x', 'name' => 'Nowhere']);

        self::assertSame('x', $place->id);
        self::assertSame('Nowhere', $place->name);
        self::assertNull($place->fullName);
        self::assertNull($place->country);
        self::assertNull($place->geo);
    }
}
