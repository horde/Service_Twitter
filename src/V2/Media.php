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
 * Media object surfaced through includes.media[] when media expansion is
 * requested. `mediaKey` is the cross-reference used by TweetAttachments.
 * `type` is one of photo|video|animated_gif. URL fields populate only for
 * the types and visibility levels Twitter exposes them on.
 */
final class Media
{
    public function __construct(
        public readonly string $mediaKey,
        public readonly string $type,
        public readonly ?string $url = null,
        public readonly ?string $previewImageUrl = null,
        public readonly ?string $altText = null,
        public readonly ?int $width = null,
        public readonly ?int $height = null,
        public readonly ?int $durationMs = null,
        /** @var array{view_count?: int, playback_0_count?: int, playback_25_count?: int, playback_50_count?: int, playback_75_count?: int, playback_100_count?: int}|null */
        public readonly ?array $publicMetrics = null,
    ) {}

    public static function fromApiResponse(object $data): self
    {
        $publicMetrics = null;
        if (isset($data->public_metrics) && is_object($data->public_metrics)) {
            $publicMetrics = (array) $data->public_metrics;
        }

        return new self(
            mediaKey: isset($data->media_key) ? (string) $data->media_key : '',
            type: isset($data->type) ? (string) $data->type : '',
            url: $data->url ?? null,
            previewImageUrl: $data->preview_image_url ?? null,
            altText: $data->alt_text ?? null,
            width: isset($data->width) ? (int) $data->width : null,
            height: isset($data->height) ? (int) $data->height : null,
            durationMs: isset($data->duration_ms) ? (int) $data->duration_ms : null,
            publicMetrics: $publicMetrics,
        );
    }
}
