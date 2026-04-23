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

class UpdateStatusParams
{
    public function __construct(
        public readonly string $status,
        public readonly ?int $inReplyToStatusId = null,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly bool $trimUser = false,
    ) {}

    /** @return array<string, string|int|float|bool> */
    public function toArray(): array
    {
        $data = ['status' => $this->status];

        if ($this->inReplyToStatusId !== null) {
            $data['in_reply_to_status_id'] = $this->inReplyToStatusId;
        }

        if ($this->latitude !== null) {
            $data['lat'] = $this->latitude;
        }

        if ($this->longitude !== null) {
            $data['long'] = $this->longitude;
        }

        if ($this->trimUser) {
            $data['trim_user'] = true;
        }

        return $data;
    }
}
