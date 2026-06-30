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
 * Field- and expansion-selection grammar shared across v2 read endpoints.
 *
 * Each list maps 1:1 to a `*.fields` query parameter and is comma-joined.
 * Empty lists are simply omitted, which gives Twitter its defaults (id +
 * text + edit_history_tweet_ids on tweets, id + name + username on users).
 * Callers wanting the rich DTO fields added in this release must include the
 * matching field names — e.g. `tweetFields: ['entities', 'attachments',
 * 'context_annotations']` and `expansions: ['attachments.media_keys',
 * 'geo.place_id']` to populate `Tweet::$entities`, `$attachments`, etc.
 */
final class TweetFieldsParams
{
    /**
     * @param list<string> $tweetFields e.g. ['created_at', 'public_metrics', 'entities']
     * @param list<string> $userFields  e.g. ['profile_image_url', 'description']
     * @param list<string> $mediaFields e.g. ['url', 'preview_image_url', 'alt_text']
     * @param list<string> $pollFields  e.g. ['options', 'voting_status']
     * @param list<string> $placeFields e.g. ['full_name', 'country', 'country_code']
     * @param list<string> $expansions  e.g. ['author_id', 'attachments.media_keys', 'geo.place_id']
     */
    public function __construct(
        public readonly array $tweetFields = [],
        public readonly array $userFields = [],
        public readonly array $mediaFields = [],
        public readonly array $pollFields = [],
        public readonly array $placeFields = [],
        public readonly array $expansions = [],
    ) {}

    /** @return array<string, string> */
    public function toQueryParams(): array
    {
        $params = [];

        if ($this->tweetFields !== []) {
            $params['tweet.fields'] = implode(',', $this->tweetFields);
        }

        if ($this->userFields !== []) {
            $params['user.fields'] = implode(',', $this->userFields);
        }

        if ($this->mediaFields !== []) {
            $params['media.fields'] = implode(',', $this->mediaFields);
        }

        if ($this->pollFields !== []) {
            $params['poll.fields'] = implode(',', $this->pollFields);
        }

        if ($this->placeFields !== []) {
            $params['place.fields'] = implode(',', $this->placeFields);
        }

        if ($this->expansions !== []) {
            $params['expansions'] = implode(',', $this->expansions);
        }

        return $params;
    }
}
