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

namespace Horde\Service\Twitter\V2\Request;

use Horde\Service\Twitter\V2\TweetFieldsParams;
use Horde\Service\Twitter\V2\TwitterApiConfig;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

final class GetUserMeRequestFactory
{
    public function __construct(
        private readonly RequestFactoryInterface $requestFactory,
        private readonly TwitterApiConfig $config,
        private readonly ?TweetFieldsParams $fields = null,
    ) {}

    public function create(): RequestInterface
    {
        $url = $this->config->baseUrl . '/2/users/me';
        $queryParams = $this->fields?->toQueryParams() ?? [];
        if ($queryParams !== []) {
            $url .= '?' . http_build_query($queryParams);
        }

        return $this->requestFactory->createRequest('GET', $url)
            ->withHeader('Accept', 'application/json');
    }
}
