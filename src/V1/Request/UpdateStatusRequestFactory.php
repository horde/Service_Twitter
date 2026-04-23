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

use Horde\Service\Twitter\V1\TwitterApiConfig;
use Horde\Service\Twitter\V1\UpdateStatusParams;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

class UpdateStatusRequestFactory
{
    public function __construct(
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly TwitterApiConfig $config,
        private readonly UpdateStatusParams $params,
    ) {}

    public function create(): RequestInterface
    {
        $url = $this->config->baseUrl . '/statuses/update.json';
        $body = http_build_query($this->params->toArray());

        return $this->requestFactory->createRequest('POST', $url)
            ->withHeader('Accept', 'application/json')
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody($this->streamFactory->createStream($body));
    }
}
