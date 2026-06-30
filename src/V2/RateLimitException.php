<?php

declare(strict_types=1);

/**
 * Copyright 2009-2026 Horde LLC (http://www.horde.org/)
 *
 * @author Michael J. Rubinsky <mrubinsk@horde.org>
 * @license http://www.horde.org/licenses/bsd BSD
 *
 * See the enclosed file LICENSE for license information (BSD). If you
 * did not receive this file, see http://www.horde.org/licenses/bsd.
 */

namespace Horde\Service\Twitter\V2;

use DateTimeImmutable;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Thrown on HTTP 429 responses from the v2 API.
 *
 * Carries the parsed rate-limit metadata Twitter exposes in response headers
 * (`x-rate-limit-limit`, `x-rate-limit-remaining`, `x-rate-limit-reset`) plus
 * the optional `Retry-After` value. Any header that is missing or malformed
 * stays null/-1 rather than throwing — the exception itself is the error
 * signal, the metadata is best-effort context.
 *
 * Subclass of `TwitterApiException`, so existing catch-all error handling
 * continues to work. Code that wants to back off intelligently catches this
 * subtype.
 */
class RateLimitException extends TwitterApiException
{
    public function __construct(
        string $message,
        int $httpStatusCode = 429,
        string $responseBody = '',
        public readonly ?int $limit = null,
        public readonly ?int $remaining = null,
        public readonly ?DateTimeImmutable $resetAt = null,
        public readonly ?int $retryAfter = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $httpStatusCode, $responseBody, $previous);
    }

    /**
     * Build an instance from a PSR-7 429 response, reading the limit headers
     * Twitter emits.
     */
    public static function fromResponse(ResponseInterface $response, string $message): self
    {
        $limit = self::intHeader($response, 'x-rate-limit-limit');
        $remaining = self::intHeader($response, 'x-rate-limit-remaining');
        $resetEpoch = self::intHeader($response, 'x-rate-limit-reset');
        $resetAt = $resetEpoch !== null
            ? (new DateTimeImmutable('@' . $resetEpoch))
            : null;
        $retryAfter = self::intHeader($response, 'retry-after');

        return new self(
            message: $message,
            httpStatusCode: $response->getStatusCode(),
            responseBody: (string) $response->getBody(),
            limit: $limit,
            remaining: $remaining,
            resetAt: $resetAt,
            retryAfter: $retryAfter,
        );
    }

    private static function intHeader(ResponseInterface $response, string $name): ?int
    {
        $value = $response->getHeaderLine($name);
        if ($value === '' || !is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }
}
