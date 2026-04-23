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

class CursorParams
{
    public function __construct(
        public readonly ?string $screenName = null,
        public readonly ?int $userId = null,
        public readonly int $cursor = -1,
        public readonly int $count = 20,
        public readonly bool $skipStatus = false,
    ) {}

    public function toQueryString(): string
    {
        $params = [];

        if ($this->screenName !== null) {
            $params['screen_name'] = $this->screenName;
        }

        if ($this->userId !== null) {
            $params['user_id'] = $this->userId;
        }

        if ($this->cursor !== -1) {
            $params['cursor'] = $this->cursor;
        }

        if ($this->count !== 20) {
            $params['count'] = $this->count;
        }

        if ($this->skipStatus) {
            $params['skip_status'] = 'true';
        }

        return http_build_query($params);
    }
}
