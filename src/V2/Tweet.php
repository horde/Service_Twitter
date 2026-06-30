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
 * A single tweet as returned by the v2 API.
 *
 * Required fields (`id`, `text`) come back on every response. The rest are
 * opt-in: Twitter only ships them when the caller asks for them through
 * `tweet.fields=…` / `expansions=…`. Anything not requested or not present
 * stays null, so callers can distinguish "not asked for" from "asked for and
 * empty" (which the API represents with an empty array / empty string).
 *
 * `attachments`, `entities`, `geo` are typed sub-DTOs; the cross-references
 * (`attachments.mediaKeys` → media_key, `geo.placeId` → place id, mentions →
 * user id) resolve against the `Includes` instance attached to the response.
 *
 * `referencedTweets` deliberately stays a plain array of {type,id} maps for
 * backwards compatibility with the original V2 client; resolve referenced
 * tweets through `Includes::tweet($id)` when the `referenced_tweets.id`
 * expansion is requested.
 */
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
        /** @var list<array{type: string, id: string}>|null */
        public readonly ?array $referencedTweets = null,
        /** @var array{retweet_count?: int, reply_count?: int, like_count?: int, quote_count?: int, impression_count?: int}|null */
        public readonly ?array $publicMetrics = null,
        /** @var list<string>|null */
        public readonly ?array $editHistoryTweetIds = null,
        public readonly ?TweetEntities $entities = null,
        public readonly ?TweetAttachments $attachments = null,
        public readonly ?TweetGeo $geo = null,
        /** @var list<array{domain: array<string, mixed>, entity: array<string, mixed>}>|null */
        public readonly ?array $contextAnnotations = null,
        public readonly ?string $replySettings = null,
        public readonly ?bool $possiblySensitive = null,
        /** @var array<string, mixed>|null */
        public readonly ?array $withheld = null,
        public readonly ?string $source = null,
        /** @var array{text: string, entities?: array<string, mixed>}|null */
        public readonly ?array $noteTweet = null,
    ) {}

    public static function fromApiResponse(object $data): self
    {
        $referencedTweets = null;
        if (isset($data->referenced_tweets) && is_array($data->referenced_tweets)) {
            $referencedTweets = array_map(
                static fn(object $ref): array => [
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

        $entities = null;
        if (isset($data->entities) && is_object($data->entities)) {
            $entities = TweetEntities::fromApiResponse($data->entities);
        }

        $attachments = null;
        if (isset($data->attachments) && is_object($data->attachments)) {
            $attachments = TweetAttachments::fromApiResponse($data->attachments);
        }

        $geo = null;
        if (isset($data->geo) && is_object($data->geo)) {
            $geo = TweetGeo::fromApiResponse($data->geo);
        }

        $contextAnnotations = null;
        if (isset($data->context_annotations) && is_array($data->context_annotations)) {
            $contextAnnotations = [];
            foreach ($data->context_annotations as $row) {
                if (!is_object($row)) {
                    continue;
                }
                $domain = isset($row->domain) && is_object($row->domain) ? (array) $row->domain : [];
                $entity = isset($row->entity) && is_object($row->entity) ? (array) $row->entity : [];
                $contextAnnotations[] = ['domain' => $domain, 'entity' => $entity];
            }
        }

        $withheld = null;
        if (isset($data->withheld) && is_object($data->withheld)) {
            $withheld = (array) $data->withheld;
        }

        $noteTweet = null;
        if (isset($data->note_tweet) && is_object($data->note_tweet)) {
            $noteEntities = null;
            if (isset($data->note_tweet->entities) && is_object($data->note_tweet->entities)) {
                $noteEntities = (array) $data->note_tweet->entities;
            }
            $noteTweet = [
                'text' => isset($data->note_tweet->text) ? (string) $data->note_tweet->text : '',
            ];
            if ($noteEntities !== null) {
                $noteTweet['entities'] = $noteEntities;
            }
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
            entities: $entities,
            attachments: $attachments,
            geo: $geo,
            contextAnnotations: $contextAnnotations,
            replySettings: $data->reply_settings ?? null,
            possiblySensitive: isset($data->possibly_sensitive) ? (bool) $data->possibly_sensitive : null,
            withheld: $withheld,
            source: $data->source ?? null,
            noteTweet: $noteTweet,
        );
    }

    public function __toString(): string
    {
        return $this->text;
    }
}
