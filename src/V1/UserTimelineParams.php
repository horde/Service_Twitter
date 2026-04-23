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

class UserTimelineParams extends TimelineParams
{
    public function __construct(
        public readonly ?string $screenName = null,
        public readonly ?int $userId = null,
        ?int $sinceId = null,
        ?int $maxId = null,
        int $count = 20,
        bool $trimUser = false,
        bool $excludeReplies = false,
        bool $includeEntities = true,
    ) {
        parent::__construct($sinceId, $maxId, $count, $trimUser, $excludeReplies, $includeEntities);
    }

    public function toQueryString(): string
    {
        $params = [];

        if ($this->screenName !== null) {
            $params['screen_name'] = $this->screenName;
        }

        if ($this->userId !== null) {
            $params['user_id'] = $this->userId;
        }

        $parentQuery = parent::toQueryString();
        $localQuery = http_build_query($params);

        if ($parentQuery !== '' && $localQuery !== '') {
            return $localQuery . '&' . $parentQuery;
        }

        return $localQuery . $parentQuery;
    }
}
