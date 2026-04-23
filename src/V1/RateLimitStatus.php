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

class RateLimitStatus
{
    /**
     * @param array<string, RateLimitResource> $resources
     */
    public function __construct(
        public readonly string $context,
        public readonly array $resources,
    ) {}

    public static function fromApiResponse(object $data): self
    {
        $resources = [];
        $context = $data->rate_limit_context->access_token ?? '';

        if (isset($data->resources) && is_object($data->resources)) {
            foreach ($data->resources as $category => $endpoints) {
                if (!is_object($endpoints)) {
                    continue;
                }
                foreach ($endpoints as $endpoint => $limits) {
                    if (!is_object($limits)) {
                        continue;
                    }
                    $resources[$endpoint] = RateLimitResource::fromApiResponse($endpoint, $limits);
                }
            }
        }

        return new self(
            context: (string) $context,
            resources: $resources,
        );
    }
}
