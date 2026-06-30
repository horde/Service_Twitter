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

/**
 * Typed bag for the v2 `includes` envelope. Holds the side-channel objects
 * Twitter ships alongside `data` when expansions are requested, keyed by
 * the identifier the parent reference uses (user.id, tweet.id, media_key,
 * poll.id, place.id) so lookups stay O(1) and explicit.
 */
final class Includes
{
    /**
     * @param array<string, User>  $users    keyed by user id
     * @param array<string, Tweet> $tweets   keyed by tweet id (referenced/quoted/etc.)
     * @param array<string, Media> $media    keyed by media_key
     * @param array<string, Poll>  $polls    keyed by poll id
     * @param array<string, Place> $places   keyed by place id
     */
    public function __construct(
        public readonly array $users = [],
        public readonly array $tweets = [],
        public readonly array $media = [],
        public readonly array $polls = [],
        public readonly array $places = [],
    ) {}

    public static function fromApiResponse(object $data): self
    {
        return new self(
            users: self::indexBy($data->users ?? [], 'id', User::fromApiResponse(...)),
            tweets: self::indexBy($data->tweets ?? [], 'id', Tweet::fromApiResponse(...)),
            media: self::indexBy($data->media ?? [], 'media_key', Media::fromApiResponse(...)),
            polls: self::indexBy($data->polls ?? [], 'id', Poll::fromApiResponse(...)),
            places: self::indexBy($data->places ?? [], 'id', Place::fromApiResponse(...)),
        );
    }

    public function user(string $id): ?User
    {
        return $this->users[$id] ?? null;
    }

    public function tweet(string $id): ?Tweet
    {
        return $this->tweets[$id] ?? null;
    }

    public function mediaByKey(string $mediaKey): ?Media
    {
        return $this->media[$mediaKey] ?? null;
    }

    public function poll(string $id): ?Poll
    {
        return $this->polls[$id] ?? null;
    }

    public function place(string $id): ?Place
    {
        return $this->places[$id] ?? null;
    }

    public function isEmpty(): bool
    {
        return $this->users === []
            && $this->tweets === []
            && $this->media === []
            && $this->polls === []
            && $this->places === [];
    }

    /**
     * Build a key→DTO map from a raw `includes.*` array. Entries with no key
     * field (or non-object payloads) are dropped silently; the API contract
     * guarantees the key, but defensive parsing keeps a single malformed
     * record from breaking the whole response.
     *
     * @template T of object
     * @param mixed $raw
     * @param callable(object): T $factory
     * @return array<string, T>
     */
    private static function indexBy(mixed $raw, string $keyField, callable $factory): array
    {
        if (!is_array($raw)) {
            return [];
        }

        $out = [];
        foreach ($raw as $item) {
            if (!is_object($item) || !isset($item->$keyField)) {
                continue;
            }
            $out[(string) $item->$keyField] = $factory($item);
        }

        return $out;
    }
}
