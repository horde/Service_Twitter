<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit;

use Horde\Service\Twitter\V2\TwitterApiException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TwitterApiException::class)]
final class TwitterApiExceptionTest extends TestCase
{
    public function testCarriesHttpStatusCode(): void
    {
        $e = new TwitterApiException('Not Found', httpStatusCode: 404, responseBody: '{"detail":"Not Found"}');

        self::assertSame(404, $e->httpStatusCode);
        self::assertSame('{"detail":"Not Found"}', $e->responseBody);
        self::assertSame('Not Found', $e->getMessage());
        self::assertSame(404, $e->getCode());
    }

    public function testDefaults(): void
    {
        $e = new TwitterApiException('error');

        self::assertSame(0, $e->httpStatusCode);
        self::assertSame('', $e->responseBody);
    }
}
