<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit;

use Horde\Service\Twitter\V2\RateLimitException;
use Horde\Service\Twitter\V2\TwitterApiException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(RateLimitException::class)]
final class RateLimitExceptionTest extends TestCase
{
    public function testIsCaughtAsTwitterApiException(): void
    {
        $e = new RateLimitException('rate limited');

        self::assertInstanceOf(TwitterApiException::class, $e);
    }

    public function testFromResponseExtractsAllHeaders(): void
    {
        $headers = [
            'x-rate-limit-limit' => '900',
            'x-rate-limit-remaining' => '0',
            'x-rate-limit-reset' => '1717000000',
            'retry-after' => '15',
        ];
        $response = $this->responseWithHeaders(429, 'rate limited', $headers);

        $e = RateLimitException::fromResponse($response, '429 Too Many Requests: rate limited');

        self::assertSame(429, $e->httpStatusCode);
        self::assertSame(900, $e->limit);
        self::assertSame(0, $e->remaining);
        self::assertNotNull($e->resetAt);
        self::assertSame(1717000000, $e->resetAt->getTimestamp());
        self::assertSame(15, $e->retryAfter);
        self::assertSame('rate limited', $e->responseBody);
    }

    public function testMissingHeadersStayNull(): void
    {
        $response = $this->responseWithHeaders(429, 'body', []);

        $e = RateLimitException::fromResponse($response, 'msg');

        self::assertNull($e->limit);
        self::assertNull($e->remaining);
        self::assertNull($e->resetAt);
        self::assertNull($e->retryAfter);
    }

    public function testNonNumericHeaderIsTreatedAsAbsent(): void
    {
        $response = $this->responseWithHeaders(429, '', ['x-rate-limit-limit' => 'whatever']);

        $e = RateLimitException::fromResponse($response, 'msg');

        self::assertNull($e->limit);
        self::assertNull($e->remaining);
    }

    /**
     * Build a response mock with real expectations: fromResponse() reads the
     * status, body, and exactly the four rate-limit header lines. Setting
     * `expects` on each method keeps PHPUnit's "no expectations" notice
     * (strict mode) from firing while still asserting the public contract.
     *
     * @param array<string, string> $headers keyed by lower-case header name
     */
    private function responseWithHeaders(int $status, string $body, array $headers): ResponseInterface
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->once())->method('__toString')->willReturn($body);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('getStatusCode')->willReturn($status);
        $response->expects($this->once())->method('getBody')->willReturn($stream);
        $response->expects($this->exactly(4))
            ->method('getHeaderLine')
            ->willReturnCallback(
                static fn (string $name): string => $headers[strtolower($name)] ?? '',
            );

        return $response;
    }
}
