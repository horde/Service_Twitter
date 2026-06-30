<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit;

use Horde\Service\Twitter\V2\Includes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Includes::class)]
final class IncludesTest extends TestCase
{
    public function testIndexesEachCollectionByItsKey(): void
    {
        $data = (object) [
            'users' => [
                (object) ['id' => '42', 'name' => 'Forty-Two', 'username' => 'fortytwo'],
            ],
            'tweets' => [
                (object) ['id' => '100', 'text' => 'referenced'],
            ],
            'media' => [
                (object) ['media_key' => '3_1', 'type' => 'photo'],
            ],
            'polls' => [
                (object) ['id' => '9', 'options' => []],
            ],
            'places' => [
                (object) ['id' => 'sf', 'name' => 'San Francisco'],
            ],
        ];

        $includes = Includes::fromApiResponse($data);

        self::assertSame('fortytwo', $includes->user('42')?->username);
        self::assertSame('referenced', $includes->tweet('100')?->text);
        self::assertSame('photo', $includes->mediaByKey('3_1')?->type);
        self::assertSame('9', $includes->poll('9')?->id);
        self::assertSame('San Francisco', $includes->place('sf')?->name);
        self::assertFalse($includes->isEmpty());
    }

    public function testEmptyResponseYieldsEmptyInstance(): void
    {
        $includes = Includes::fromApiResponse((object) []);

        self::assertTrue($includes->isEmpty());
        self::assertNull($includes->user('1'));
        self::assertNull($includes->mediaByKey('x'));
    }

    public function testItemsWithoutKeyFieldAreDropped(): void
    {
        $data = (object) [
            'users' => [
                (object) ['name' => 'no-id'],
                (object) ['id' => '7', 'name' => 'Seven', 'username' => 'seven'],
            ],
        ];

        $includes = Includes::fromApiResponse($data);

        self::assertCount(1, $includes->users);
        self::assertSame('seven', $includes->user('7')?->username);
    }
}
