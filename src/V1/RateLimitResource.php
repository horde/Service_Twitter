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

namespace Horde\Service\Twitter\V1;

class RateLimitResource
{
    public function __construct(
        public readonly string $endpoint,
        public readonly int $limit,
        public readonly int $remaining,
        public readonly int $reset,
    ) {}

    public static function fromApiResponse(string $endpoint, object $data): self
    {
        return new self(
            endpoint: $endpoint,
            limit: (int) ($data->limit ?? 0),
            remaining: (int) ($data->remaining ?? 0),
            reset: (int) ($data->reset ?? 0),
        );
    }

    public function isExhausted(): bool
    {
        return $this->remaining <= 0;
    }

    public function getSecondsUntilReset(): int
    {
        return max(0, $this->reset - time());
    }
}
