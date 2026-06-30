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

use DateTimeInterface;
use DateTimeImmutable;
use DateTimeZone;

/**
 * Query parameters shared by `GET /2/users/{id}/tweets` and the bookmark
 * timeline. `exclude` accepts the API names ("retweets", "replies") joined
 * with commas. `startTime` and `endTime` accept any DateTimeInterface and
 * emit the ISO 8601 form Twitter requires (UTC, second precision); they are
 * mutually exclusive with `sinceId`/`untilId` per the Twitter docs, but we
 * leave that policy to the caller rather than rejecting it client-side.
 */
final class UserTimelineParams
{
    /**
     * @param list<string>|null $exclude e.g. ['retweets', 'replies']
     */
    public function __construct(
        public readonly ?int $maxResults = null,
        public readonly ?string $paginationToken = null,
        public readonly ?string $sinceId = null,
        public readonly ?string $untilId = null,
        public readonly ?array $exclude = null,
        public readonly ?DateTimeInterface $startTime = null,
        public readonly ?DateTimeInterface $endTime = null,
    ) {}

    /** @return array<string, string> */
    public function toQueryParams(): array
    {
        $params = [];

        if ($this->maxResults !== null) {
            $params['max_results'] = (string) $this->maxResults;
        }

        if ($this->paginationToken !== null) {
            $params['pagination_token'] = $this->paginationToken;
        }

        if ($this->sinceId !== null) {
            $params['since_id'] = $this->sinceId;
        }

        if ($this->untilId !== null) {
            $params['until_id'] = $this->untilId;
        }

        if ($this->exclude !== null && $this->exclude !== []) {
            $params['exclude'] = implode(',', $this->exclude);
        }

        if ($this->startTime !== null) {
            $params['start_time'] = self::formatTime($this->startTime);
        }

        if ($this->endTime !== null) {
            $params['end_time'] = self::formatTime($this->endTime);
        }

        return $params;
    }

    /**
     * Twitter wants ISO 8601 in UTC: YYYY-MM-DDTHH:MM:SSZ (no fractional
     * seconds, no offset). Clone before mutating so the caller's DateTime
     * stays untouched.
     */
    private static function formatTime(DateTimeInterface $time): string
    {
        $utc = (new DateTimeImmutable('@' . $time->getTimestamp()))->setTimezone(
            new DateTimeZone('UTC'),
        );

        return $utc->format('Y-m-d\TH:i:s\Z');
    }
}
