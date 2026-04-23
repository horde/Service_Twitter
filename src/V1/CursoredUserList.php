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

class CursoredUserList
{
    public function __construct(
        public readonly TwitterUserList $users,
        public readonly int $nextCursor,
        public readonly int $previousCursor,
    ) {}

    public static function fromApiResponse(object $data): self
    {
        $users = [];
        if (isset($data->users) && is_array($data->users)) {
            foreach ($data->users as $userData) {
                $users[] = TwitterUser::fromApiResponse($userData);
            }
        }

        return new self(
            users: new TwitterUserList($users),
            nextCursor: (int) ($data->next_cursor ?? 0),
            previousCursor: (int) ($data->previous_cursor ?? 0),
        );
    }

    public function hasMore(): bool
    {
        return $this->nextCursor !== 0;
    }
}
