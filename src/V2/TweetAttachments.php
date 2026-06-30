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
 * Tweet attachments block: keys into the response's includes.media[] and
 * includes.polls[] collections. Resolve via Includes::media($key) / poll($id)
 * after a request that asks for the corresponding expansion.
 */
final class TweetAttachments
{
    public function __construct(
        /** @var list<string>|null */
        public readonly ?array $mediaKeys = null,
        /** @var list<string>|null */
        public readonly ?array $pollIds = null,
    ) {}

    public static function fromApiResponse(object $data): self
    {
        return new self(
            mediaKeys: self::stringList($data->media_keys ?? null),
            pollIds: self::stringList($data->poll_ids ?? null),
        );
    }

    /**
     * @return list<string>|null
     */
    private static function stringList(mixed $value): ?array
    {
        if (!is_array($value)) {
            return null;
        }

        return array_values(array_map(strval(...), $value));
    }
}
