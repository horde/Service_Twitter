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
 * Typed entities block emitted by the v2 API when tweet.fields=entities is
 * requested. Each list is a normalised array of associative entries; the
 * shapes mirror what Twitter returns for {urls,hashtags,mentions,cashtags,
 * annotations}. Missing arrays in the response stay null so callers can tell
 * "not requested" from "requested but empty".
 */
final class TweetEntities
{
    public function __construct(
        /** @var list<array{start: int, end: int, url: string, expanded_url?: string, display_url?: string, unwound_url?: string, title?: string, description?: string, media_key?: string}>|null */
        public readonly ?array $urls = null,
        /** @var list<array{start: int, end: int, tag: string}>|null */
        public readonly ?array $hashtags = null,
        /** @var list<array{start: int, end: int, username: string, id?: string}>|null */
        public readonly ?array $mentions = null,
        /** @var list<array{start: int, end: int, tag: string}>|null */
        public readonly ?array $cashtags = null,
        /** @var list<array{start: int, end: int, probability: float, type: string, normalized_text: string}>|null */
        public readonly ?array $annotations = null,
    ) {}

    public static function fromApiResponse(object $data): self
    {
        return new self(
            urls: self::normaliseList($data->urls ?? null),
            hashtags: self::normaliseList($data->hashtags ?? null),
            mentions: self::normaliseList($data->mentions ?? null),
            cashtags: self::normaliseList($data->cashtags ?? null),
            annotations: self::normaliseList($data->annotations ?? null),
        );
    }

    /**
     * @return list<array<string, mixed>>|null
     */
    private static function normaliseList(mixed $value): ?array
    {
        if (!is_array($value)) {
            return null;
        }

        return array_map(
            static fn (object $item): array => (array) $item,
            $value,
        );
    }
}
