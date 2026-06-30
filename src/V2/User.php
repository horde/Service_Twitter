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

use Stringable;

final class User implements Stringable
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $username,
        public readonly ?string $profileImageUrl = null,
        public readonly ?string $description = null,
        public readonly ?string $createdAt = null,
        public readonly ?bool $verified = null,
        /** @var array{followers_count?: int, following_count?: int, tweet_count?: int, listed_count?: int}|null */
        public readonly ?array $publicMetrics = null,
    ) {}

    public static function fromApiResponse(object $data): self
    {
        $publicMetrics = null;
        if (isset($data->public_metrics) && is_object($data->public_metrics)) {
            $publicMetrics = (array) $data->public_metrics;
        }

        return new self(
            id: $data->id ?? '',
            name: $data->name ?? '',
            username: $data->username ?? '',
            profileImageUrl: $data->profile_image_url ?? null,
            description: $data->description ?? null,
            createdAt: $data->created_at ?? null,
            verified: isset($data->verified) ? (bool) $data->verified : null,
            publicMetrics: $publicMetrics,
        );
    }

    public function __toString(): string
    {
        return '@' . $this->username;
    }
}
