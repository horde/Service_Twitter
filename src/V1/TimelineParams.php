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

class TimelineParams
{
    public function __construct(
        public readonly ?int $sinceId = null,
        public readonly ?int $maxId = null,
        public readonly int $count = 20,
        public readonly bool $trimUser = false,
        public readonly bool $excludeReplies = false,
        public readonly bool $includeEntities = true,
    ) {}

    public function toQueryString(): string
    {
        $params = [];

        if ($this->sinceId !== null) {
            $params['since_id'] = $this->sinceId;
        }

        if ($this->maxId !== null) {
            $params['max_id'] = $this->maxId;
        }

        if ($this->count !== 20) {
            $params['count'] = $this->count;
        }

        if ($this->trimUser) {
            $params['trim_user'] = 'true';
        }

        if ($this->excludeReplies) {
            $params['exclude_replies'] = 'true';
        }

        if (!$this->includeEntities) {
            $params['include_entities'] = 'false';
        }

        return http_build_query($params);
    }
}
