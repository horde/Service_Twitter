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

final class CreateTweetParams
{
    public function __construct(
        public readonly string $text,
        public readonly ?string $quoteTweetId = null,
        public readonly ?string $inReplyToTweetId = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = ['text' => $this->text];

        if ($this->quoteTweetId !== null) {
            $data['quote_tweet_id'] = $this->quoteTweetId;
        }

        if ($this->inReplyToTweetId !== null) {
            $data['reply'] = ['in_reply_to_tweet_id' => $this->inReplyToTweetId];
        }

        return $data;
    }
}
