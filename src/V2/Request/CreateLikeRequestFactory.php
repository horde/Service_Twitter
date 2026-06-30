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

use Horde\Service\Twitter\V2\TwitterApiConfig;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class CreateLikeRequestFactory
{
    public function __construct(
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly TwitterApiConfig $config,
        private readonly string $userId,
        private readonly string $tweetId,
    ) {}

    public function create(): RequestInterface
    {
        $url = $this->config->baseUrl . '/2/users/' . $this->userId . '/likes';
        $body = $this->streamFactory->createStream(
            json_encode(['tweet_id' => $this->tweetId], JSON_THROW_ON_ERROR),
        );

        return $this->requestFactory->createRequest('POST', $url)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withBody($body);
    }
}
