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
 * Place surfaced through includes.places[] when the geo.place_id expansion
 * is requested. `geo` holds the raw bounding box / geometry block Twitter
 * returns; we keep it as an associative array rather than introducing a
 * dedicated GeoJSON DTO until a caller actually needs typed access.
 */
final class Place
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $fullName = null,
        public readonly ?string $country = null,
        public readonly ?string $countryCode = null,
        public readonly ?string $placeType = null,
        /** @var array<string, mixed>|null */
        public readonly ?array $geo = null,
    ) {}

    public static function fromApiResponse(object $data): self
    {
        $geo = null;
        if (isset($data->geo) && is_object($data->geo)) {
            $geo = json_decode(json_encode($data->geo, JSON_THROW_ON_ERROR), true);
        }

        return new self(
            id: isset($data->id) ? (string) $data->id : '',
            name: isset($data->name) ? (string) $data->name : '',
            fullName: $data->full_name ?? null,
            country: $data->country ?? null,
            countryCode: $data->country_code ?? null,
            placeType: $data->place_type ?? null,
            geo: $geo,
        );
    }
}
