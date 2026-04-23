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

use Stringable;

class TwitterUser implements Stringable
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $screenName,
        public readonly string $description,
        public readonly string $profileImageUrl,
        public readonly int $followersCount,
        public readonly int $friendsCount,
        public readonly int $statusesCount,
        public readonly string $createdAt,
        public readonly bool $verified,
        public readonly bool $protected,
    ) {}

    public static function fromApiResponse(object $data): self
    {
        return new self(
            id: (int) ($data->id ?? 0),
            name: $data->name ?? '',
            screenName: $data->screen_name ?? '',
            description: $data->description ?? '',
            profileImageUrl: $data->profile_image_url_https ?? $data->profile_image_url ?? '',
            followersCount: (int) ($data->followers_count ?? 0),
            friendsCount: (int) ($data->friends_count ?? 0),
            statusesCount: (int) ($data->statuses_count ?? 0),
            createdAt: $data->created_at ?? '',
            verified: (bool) ($data->verified ?? false),
            protected: (bool) ($data->protected ?? false),
        );
    }

    public function __toString(): string
    {
        return '@' . $this->screenName;
    }
}
