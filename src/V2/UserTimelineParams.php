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

final class UserTimelineParams
{
    public function __construct(
        public readonly ?int $maxResults = null,
        public readonly ?string $paginationToken = null,
        public readonly ?string $sinceId = null,
        public readonly ?string $untilId = null,
    ) {}

    /** @return array<string, string> */
    public function toQueryParams(): array
    {
        $params = [];

        if ($this->maxResults !== null) {
            $params['max_results'] = (string) $this->maxResults;
        }

        if ($this->paginationToken !== null) {
            $params['pagination_token'] = $this->paginationToken;
        }

        if ($this->sinceId !== null) {
            $params['since_id'] = $this->sinceId;
        }

        if ($this->untilId !== null) {
            $params['until_id'] = $this->untilId;
        }

        return $params;
    }
}
