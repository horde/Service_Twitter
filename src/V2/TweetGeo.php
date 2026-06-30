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
 * Tweet geo block: a place reference plus optional GeoJSON-style coordinates.
 * Resolve placeId via Includes::place($id) after requesting the
 * geo.place_id expansion.
 */
final class TweetGeo
{
    public function __construct(
        public readonly ?string $placeId = null,
        /** @var array{type: string, coordinates: list<float>}|null */
        public readonly ?array $coordinates = null,
    ) {}

    public static function fromApiResponse(object $data): self
    {
        $coordinates = null;
        if (isset($data->coordinates) && is_object($data->coordinates)) {
            $type = isset($data->coordinates->type) ? (string) $data->coordinates->type : '';
            $coords = $data->coordinates->coordinates ?? [];
            if (is_array($coords)) {
                $coordinates = [
                    'type' => $type,
                    'coordinates' => array_values(array_map(floatval(...), $coords)),
                ];
            }
        }

        return new self(
            placeId: isset($data->place_id) ? (string) $data->place_id : null,
            coordinates: $coordinates,
        );
    }
}
