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

class Tweet implements Stringable
{
    public function __construct(
        public readonly int $id,
        public readonly string $idStr,
        public readonly string $text,
        public readonly TwitterUser $user,
        public readonly string $createdAt,
        public readonly ?int $inReplyToStatusId,
        public readonly ?int $inReplyToUserId,
        public readonly ?string $inReplyToScreenName,
        public readonly int $retweetCount,
        public readonly int $favoriteCount,
        public readonly bool $retweeted,
        public readonly bool $favorited,
        public readonly ?self $retweetedStatus,
        public readonly string $source,
    ) {}

    public static function fromApiResponse(object $data): self
    {
        $retweetedStatus = null;
        if (isset($data->retweeted_status)) {
            $retweetedStatus = self::fromApiResponse($data->retweeted_status);
        }

        return new self(
            id: (int) ($data->id ?? 0),
            idStr: $data->id_str ?? (string) ($data->id ?? '0'),
            text: $data->full_text ?? $data->text ?? '',
            user: TwitterUser::fromApiResponse($data->user ?? (object) []),
            createdAt: $data->created_at ?? '',
            inReplyToStatusId: isset($data->in_reply_to_status_id) ? (int) $data->in_reply_to_status_id : null,
            inReplyToUserId: isset($data->in_reply_to_user_id) ? (int) $data->in_reply_to_user_id : null,
            inReplyToScreenName: $data->in_reply_to_screen_name ?? null,
            retweetCount: (int) ($data->retweet_count ?? 0),
            favoriteCount: (int) ($data->favorite_count ?? 0),
            retweeted: (bool) ($data->retweeted ?? false),
            favorited: (bool) ($data->favorited ?? false),
            retweetedStatus: $retweetedStatus,
            source: $data->source ?? '',
        );
    }

    public function __toString(): string
    {
        return $this->text;
    }
}
