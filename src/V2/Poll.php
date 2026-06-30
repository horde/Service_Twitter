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
 * Poll attached to a tweet. Surfaces through includes.polls[] when the
 * attachments.poll_ids expansion is requested. `votingStatus` is "open"
 * while a poll is live, otherwise "closed".
 */
final class Poll
{
    public function __construct(
        public readonly string $id,
        /** @var list<array{position: int, label: string, votes: int}> */
        public readonly array $options,
        public readonly ?string $votingStatus = null,
        public readonly ?string $endDatetime = null,
        public readonly ?int $durationMinutes = null,
    ) {}

    public static function fromApiResponse(object $data): self
    {
        $options = [];
        if (isset($data->options) && is_array($data->options)) {
            foreach ($data->options as $option) {
                if (!is_object($option)) {
                    continue;
                }
                $options[] = [
                    'position' => isset($option->position) ? (int) $option->position : 0,
                    'label' => isset($option->label) ? (string) $option->label : '',
                    'votes' => isset($option->votes) ? (int) $option->votes : 0,
                ];
            }
        }

        return new self(
            id: isset($data->id) ? (string) $data->id : '',
            options: $options,
            votingStatus: $data->voting_status ?? null,
            endDatetime: $data->end_datetime ?? null,
            durationMinutes: isset($data->duration_minutes) ? (int) $data->duration_minutes : null,
        );
    }
}
