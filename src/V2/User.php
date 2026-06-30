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

/**
 * A user object from the v2 API.
 *
 * `id`, `name`, `username` are guaranteed; everything else is opt-in through
 * `user.fields=…` and stays null when not requested. `entities` parses both
 * `entities.url` and `entities.description` (URLs in the profile URL field
 * and in the bio respectively) into the same TweetEntities shape — Twitter
 * reuses the entity grammar here.
 */
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
        public readonly ?string $location = null,
        public readonly ?string $url = null,
        public readonly ?string $pinnedTweetId = null,
        public readonly ?bool $protected = null,
        /** @var array<string, mixed>|null */
        public readonly ?array $withheld = null,
        /** @var array{url?: TweetEntities, description?: TweetEntities}|null */
        public readonly ?array $entities = null,
    ) {}

    public static function fromApiResponse(object $data): self
    {
        $publicMetrics = null;
        if (isset($data->public_metrics) && is_object($data->public_metrics)) {
            $publicMetrics = (array) $data->public_metrics;
        }

        $withheld = null;
        if (isset($data->withheld) && is_object($data->withheld)) {
            $withheld = (array) $data->withheld;
        }

        $entities = null;
        if (isset($data->entities) && is_object($data->entities)) {
            $entities = [];
            if (isset($data->entities->url) && is_object($data->entities->url)) {
                $entities['url'] = TweetEntities::fromApiResponse($data->entities->url);
            }
            if (isset($data->entities->description) && is_object($data->entities->description)) {
                $entities['description'] = TweetEntities::fromApiResponse($data->entities->description);
            }
            if ($entities === []) {
                $entities = null;
            }
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
            location: $data->location ?? null,
            url: $data->url ?? null,
            pinnedTweetId: $data->pinned_tweet_id ?? null,
            protected: isset($data->protected) ? (bool) $data->protected : null,
            withheld: $withheld,
            entities: $entities,
        );
    }

    public function __toString(): string
    {
        return '@' . $this->username;
    }
}
