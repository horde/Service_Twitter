<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V1\Test\Unit;

use Horde\Service\Twitter\V1\UpdateStatusParams;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UpdateStatusParams::class)]
final class UpdateStatusParamsTest extends TestCase
{
    public function testToArrayIncludesStatus(): void
    {
        $params = new UpdateStatusParams(status: 'Hello world');

        $array = $params->toArray();

        self::assertSame('Hello world', $array['status']);
    }

    public function testToArrayOmitsNullOptionals(): void
    {
        $params = new UpdateStatusParams(status: 'Test');

        $array = $params->toArray();

        self::assertArrayNotHasKey('in_reply_to_status_id', $array);
        self::assertArrayNotHasKey('lat', $array);
        self::assertArrayNotHasKey('long', $array);
        self::assertArrayNotHasKey('trim_user', $array);
        self::assertCount(1, $array);
    }

    public function testToArrayIncludesReplyId(): void
    {
        $params = new UpdateStatusParams(status: 'Reply', inReplyToStatusId: 12345);

        $array = $params->toArray();

        self::assertSame(12345, $array['in_reply_to_status_id']);
    }

    public function testToArrayIncludesCoordinates(): void
    {
        $params = new UpdateStatusParams(
            status: 'Geotagged',
            latitude: 37.7749,
            longitude: -122.4194,
        );

        $array = $params->toArray();

        self::assertSame(37.7749, $array['lat']);
        self::assertSame(-122.4194, $array['long']);
    }

    public function testToArrayIncludesTrimUser(): void
    {
        $params = new UpdateStatusParams(status: 'Trimmed', trimUser: true);

        $array = $params->toArray();

        self::assertTrue($array['trim_user']);
    }
}
