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

use InvalidArgumentException;

/**
 * Body of a POST /2/tweets request.
 *
 * Only `text` is required by Twitter when no media or poll is attached; a
 * media-only or poll-only tweet may have empty text. The constructor accepts
 * any combination the API supports, but validates the mutually exclusive
 * groups Twitter enforces:
 *
 *   - quoteTweetId + inReplyToTweetId may both be present (quote-replies)
 *   - mediaIds + pollOptions are mutually exclusive (Twitter rejects both)
 *
 * `toArray()` shapes the nested objects (`reply`, `media`, `poll`, `geo`)
 * the v2 endpoint expects.
 */
final class CreateTweetParams
{
    public function __construct(
        public readonly string $text = '',
        public readonly ?string $quoteTweetId = null,
        public readonly ?string $inReplyToTweetId = null,
        /** @var list<string>|null */
        public readonly ?array $mediaIds = null,
        /** @var list<string>|null */
        public readonly ?array $taggedUserIds = null,
        /** @var list<string>|null */
        public readonly ?array $pollOptions = null,
        public readonly ?int $pollDurationMinutes = null,
        public readonly ?ReplySetting $replySettings = null,
        public readonly ?string $placeId = null,
        public readonly ?bool $forSuperFollowersOnly = null,
        public readonly ?string $directMessageDeepLink = null,
        /** @var list<string>|null */
        public readonly ?array $excludeReplyUserIds = null,
    ) {
        if ($mediaIds !== null && $pollOptions !== null) {
            throw new InvalidArgumentException(
                'A tweet cannot include both media and a poll; pick one.',
            );
        }
        if ($pollOptions !== null && $pollDurationMinutes === null) {
            throw new InvalidArgumentException(
                'pollDurationMinutes is required when pollOptions is set.',
            );
        }
        if ($pollOptions === null && $pollDurationMinutes !== null) {
            throw new InvalidArgumentException(
                'pollDurationMinutes only applies when pollOptions is set.',
            );
        }
        if ($text === '' && $mediaIds === null && $pollOptions === null) {
            throw new InvalidArgumentException(
                'A tweet must have text, media, or a poll.',
            );
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [];

        if ($this->text !== '') {
            $data['text'] = $this->text;
        }

        if ($this->quoteTweetId !== null) {
            $data['quote_tweet_id'] = $this->quoteTweetId;
        }

        $reply = [];
        if ($this->inReplyToTweetId !== null) {
            $reply['in_reply_to_tweet_id'] = $this->inReplyToTweetId;
        }
        if ($this->excludeReplyUserIds !== null) {
            $reply['exclude_reply_user_ids'] = $this->excludeReplyUserIds;
        }
        if ($reply !== []) {
            $data['reply'] = $reply;
        }

        if ($this->mediaIds !== null) {
            $media = ['media_ids' => $this->mediaIds];
            if ($this->taggedUserIds !== null) {
                $media['tagged_user_ids'] = $this->taggedUserIds;
            }
            $data['media'] = $media;
        }

        if ($this->pollOptions !== null) {
            $data['poll'] = [
                'options' => $this->pollOptions,
                'duration_minutes' => $this->pollDurationMinutes,
            ];
        }

        if ($this->replySettings !== null) {
            $data['reply_settings'] = $this->replySettings->value;
        }

        if ($this->placeId !== null) {
            $data['geo'] = ['place_id' => $this->placeId];
        }

        if ($this->forSuperFollowersOnly !== null) {
            $data['for_super_followers_only'] = $this->forSuperFollowersOnly;
        }

        if ($this->directMessageDeepLink !== null) {
            $data['direct_message_deep_link'] = $this->directMessageDeepLink;
        }

        return $data;
    }
}
