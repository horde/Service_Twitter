<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V1\Test\Unit;

use Horde\Service\Twitter\V1\TwitterApiException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TwitterApiException::class)]
final class TwitterApiExceptionTest extends TestCase
{
    public function testCarriesHttpStatusCodeAndBody(): void
    {
        $exception = new TwitterApiException(
            'Not Found',
            httpStatusCode: 404,
            responseBody: '{"errors":[{"message":"Not found","code":34}]}',
        );

        self::assertSame('Not Found', $exception->getMessage());
        self::assertSame(404, $exception->httpStatusCode);
        self::assertSame('{"errors":[{"message":"Not found","code":34}]}', $exception->responseBody);
        self::assertSame(404, $exception->getCode());
    }

    public function testDefaultValues(): void
    {
        $exception = new TwitterApiException('Something went wrong');

        self::assertSame(0, $exception->httpStatusCode);
        self::assertSame('', $exception->responseBody);
        self::assertNull($exception->getPrevious());
    }
}
