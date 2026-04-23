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

namespace Horde\Service\Twitter\V1\Request;

use Horde\Service\Twitter\V1\TimelineParams;
use Horde\Service\Twitter\V1\TwitterApiConfig;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

class ListFavoritesRequestFactory
{
    public function __construct(
        private readonly RequestFactoryInterface $requestFactory,
        private readonly TwitterApiConfig $config,
        private readonly TimelineParams $params,
    ) {}

    public function create(): RequestInterface
    {
        $url = $this->config->baseUrl . '/favorites/list.json';
        $query = $this->params->toQueryString();
        if ($query !== '') {
            $url .= '?' . $query;
        }

        return $this->requestFactory->createRequest('GET', $url)
            ->withHeader('Accept', 'application/json');
    }
}
