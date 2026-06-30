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

final class PaginationMeta
{
    public function __construct(
        public readonly ?string $nextToken = null,
        public readonly ?string $previousToken = null,
        public readonly int $resultCount = 0,
    ) {}

    public static function fromApiResponse(object $meta): self
    {
        return new self(
            nextToken: $meta->next_token ?? null,
            previousToken: $meta->previous_token ?? null,
            resultCount: (int) ($meta->result_count ?? 0),
        );
    }

    public function hasNextPage(): bool
    {
        return $this->nextToken !== null;
    }
}
