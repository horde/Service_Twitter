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

final class Tweet implements Stringable
{
    public function __construct(
        public readonly string $id,
        public readonly string $text,
        public readonly ?string $authorId = null,
        public readonly ?string $conversationId = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $lang = null,
        public readonly ?string $inReplyToUserId = null,
        /** @var list<object{type: string, id: string}>|null */
        public readonly ?array $referencedTweets = null,
        /** @var array{retweet_count?: int, reply_count?: int, like_count?: int, quote_count?: int}|null */
        public readonly ?array $publicMetrics = null,
        /** @var list<string>|null */
        public readonly ?array $editHistoryTweetIds = null,
    ) {}

    public static function fromApiResponse(object $data): self
    {
        $referencedTweets = null;
        if (isset($data->referenced_tweets) && is_array($data->referenced_tweets)) {
            $referencedTweets = array_map(
                static fn (object $ref): array => [
                    'type' => $ref->type ?? '',
                    'id' => $ref->id ?? '',
                ],
                $data->referenced_tweets,
            );
        }

        $publicMetrics = null;
        if (isset($data->public_metrics) && is_object($data->public_metrics)) {
            $publicMetrics = (array) $data->public_metrics;
        }

        $editHistoryTweetIds = null;
        if (isset($data->edit_history_tweet_ids) && is_array($data->edit_history_tweet_ids)) {
            $editHistoryTweetIds = $data->edit_history_tweet_ids;
        }

        return new self(
            id: $data->id ?? '',
            text: $data->text ?? '',
            authorId: $data->author_id ?? null,
            conversationId: $data->conversation_id ?? null,
            createdAt: $data->created_at ?? null,
            lang: $data->lang ?? null,
            inReplyToUserId: $data->in_reply_to_user_id ?? null,
            referencedTweets: $referencedTweets,
            publicMetrics: $publicMetrics,
            editHistoryTweetIds: $editHistoryTweetIds,
        );
    }

    public function __toString(): string
    {
        return $this->text;
    }
}
