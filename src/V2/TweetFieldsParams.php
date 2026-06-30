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

final class TweetFieldsParams
{
    /**
     * @param list<string> $tweetFields e.g. ['created_at', 'public_metrics', 'author_id']
     * @param list<string> $userFields  e.g. ['profile_image_url', 'description']
     * @param list<string> $expansions  e.g. ['author_id', 'referenced_tweets.id']
     */
    public function __construct(
        public readonly array $tweetFields = [],
        public readonly array $userFields = [],
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

        if ($this->expansions !== []) {
            $params['expansions'] = implode(',', $this->expansions);
        }

        return $params;
    }
}
