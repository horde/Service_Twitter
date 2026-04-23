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

use Horde\Service\Twitter\V1\Request\CreateFavoriteRequestFactory;
use Horde\Service\Twitter\V1\Request\DestroyFavoriteRequestFactory;
use Horde\Service\Twitter\V1\Request\DestroyTweetRequestFactory;
use Horde\Service\Twitter\V1\Request\FollowersListRequestFactory;
use Horde\Service\Twitter\V1\Request\FriendsListRequestFactory;
use Horde\Service\Twitter\V1\Request\HomeTimelineRequestFactory;
use Horde\Service\Twitter\V1\Request\ListFavoritesRequestFactory;
use Horde\Service\Twitter\V1\Request\MentionsTimelineRequestFactory;
use Horde\Service\Twitter\V1\Request\RateLimitStatusRequestFactory;
use Horde\Service\Twitter\V1\Request\RetweetRequestFactory;
use Horde\Service\Twitter\V1\Request\RetweetsOfMeRequestFactory;
use Horde\Service\Twitter\V1\Request\ShowTweetRequestFactory;
use Horde\Service\Twitter\V1\Request\UpdateStatusRequestFactory;
use Horde\Service\Twitter\V1\Request\UserTimelineRequestFactory;
use Horde\Service\Twitter\V1\Request\VerifyCredentialsRequestFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Throwable;

class TwitterApiClient
{
    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly TwitterApiConfig $config,
        private readonly ?StreamFactoryInterface $streamFactory = null,
    ) {}

    public static function create(
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        ?StreamFactoryInterface $streamFactory = null,
        string $baseUrl = 'https://api.twitter.com/1.1',
    ): self {
        return new self(
            $httpClient,
            $requestFactory,
            new TwitterApiConfig(baseUrl: $baseUrl),
            $streamFactory,
        );
    }

    public function verifyCredentials(): TwitterUser
    {
        $factory = new VerifyCredentialsRequestFactory($this->requestFactory, $this->config);
        $response = $this->httpClient->sendRequest($factory->create());

        if ($response->getStatusCode() === 200) {
            return TwitterUser::fromApiResponse(json_decode((string) $response->getBody()));
        }

        throw $this->createException($response);
    }

    public function getRateLimitStatus(): RateLimitStatus
    {
        $factory = new RateLimitStatusRequestFactory($this->requestFactory, $this->config);
        $response = $this->httpClient->sendRequest($factory->create());

        if ($response->getStatusCode() === 200) {
            return RateLimitStatus::fromApiResponse(json_decode((string) $response->getBody()));
        }

        throw $this->createException($response);
    }

    public function showTweet(int $id): Tweet
    {
        $factory = new ShowTweetRequestFactory($this->requestFactory, $this->config, $id);
        $response = $this->httpClient->sendRequest($factory->create());

        if ($response->getStatusCode() === 200) {
            return Tweet::fromApiResponse(json_decode((string) $response->getBody()));
        }

        throw $this->createException($response);
    }

    public function destroyTweet(int $id): Tweet
    {
        $factory = new DestroyTweetRequestFactory(
            $this->requestFactory,
            $this->ensureStreamFactory('destroyTweet'),
            $this->config,
            $id,
        );
        $response = $this->httpClient->sendRequest($factory->create());

        if ($response->getStatusCode() === 200) {
            return Tweet::fromApiResponse(json_decode((string) $response->getBody()));
        }

        throw $this->createException($response);
    }

    public function updateStatus(UpdateStatusParams $params): Tweet
    {
        $factory = new UpdateStatusRequestFactory(
            $this->requestFactory,
            $this->ensureStreamFactory('updateStatus'),
            $this->config,
            $params,
        );
        $response = $this->httpClient->sendRequest($factory->create());

        if ($response->getStatusCode() === 200) {
            return Tweet::fromApiResponse(json_decode((string) $response->getBody()));
        }

        throw $this->createException($response);
    }

    public function getHomeTimeline(TimelineParams $params = new TimelineParams()): TweetList
    {
        $factory = new HomeTimelineRequestFactory($this->requestFactory, $this->config, $params);
        $response = $this->httpClient->sendRequest($factory->create());

        if ($response->getStatusCode() === 200) {
            return $this->parseTweetList($response);
        }

        throw $this->createException($response);
    }

    public function getUserTimeline(UserTimelineParams $params = new UserTimelineParams()): TweetList
    {
        $factory = new UserTimelineRequestFactory($this->requestFactory, $this->config, $params);
        $response = $this->httpClient->sendRequest($factory->create());

        if ($response->getStatusCode() === 200) {
            return $this->parseTweetList($response);
        }

        throw $this->createException($response);
    }

    public function getMentionsTimeline(TimelineParams $params = new TimelineParams()): TweetList
    {
        $factory = new MentionsTimelineRequestFactory($this->requestFactory, $this->config, $params);
        $response = $this->httpClient->sendRequest($factory->create());

        if ($response->getStatusCode() === 200) {
            return $this->parseTweetList($response);
        }

        throw $this->createException($response);
    }

    public function getRetweetsOfMe(TimelineParams $params = new TimelineParams()): TweetList
    {
        $factory = new RetweetsOfMeRequestFactory($this->requestFactory, $this->config, $params);
        $response = $this->httpClient->sendRequest($factory->create());

        if ($response->getStatusCode() === 200) {
            return $this->parseTweetList($response);
        }

        throw $this->createException($response);
    }

    public function retweet(int $id): Tweet
    {
        $factory = new RetweetRequestFactory(
            $this->requestFactory,
            $this->ensureStreamFactory('retweet'),
            $this->config,
            $id,
        );
        $response = $this->httpClient->sendRequest($factory->create());

        if ($response->getStatusCode() === 200) {
            return Tweet::fromApiResponse(json_decode((string) $response->getBody()));
        }

        throw $this->createException($response);
    }

    public function getFriends(CursorParams $params = new CursorParams()): CursoredUserList
    {
        $factory = new FriendsListRequestFactory($this->requestFactory, $this->config, $params);
        $response = $this->httpClient->sendRequest($factory->create());

        if ($response->getStatusCode() === 200) {
            return CursoredUserList::fromApiResponse(json_decode((string) $response->getBody()));
        }

        throw $this->createException($response);
    }

    public function getFollowers(CursorParams $params = new CursorParams()): CursoredUserList
    {
        $factory = new FollowersListRequestFactory($this->requestFactory, $this->config, $params);
        $response = $this->httpClient->sendRequest($factory->create());

        if ($response->getStatusCode() === 200) {
            return CursoredUserList::fromApiResponse(json_decode((string) $response->getBody()));
        }

        throw $this->createException($response);
    }

    public function getFavorites(TimelineParams $params = new TimelineParams()): TweetList
    {
        $factory = new ListFavoritesRequestFactory($this->requestFactory, $this->config, $params);
        $response = $this->httpClient->sendRequest($factory->create());

        if ($response->getStatusCode() === 200) {
            return $this->parseTweetList($response);
        }

        throw $this->createException($response);
    }

    public function createFavorite(int $id): Tweet
    {
        $factory = new CreateFavoriteRequestFactory(
            $this->requestFactory,
            $this->ensureStreamFactory('createFavorite'),
            $this->config,
            $id,
        );
        $response = $this->httpClient->sendRequest($factory->create());

        if ($response->getStatusCode() === 200) {
            return Tweet::fromApiResponse(json_decode((string) $response->getBody()));
        }

        throw $this->createException($response);
    }

    public function destroyFavorite(int $id): Tweet
    {
        $factory = new DestroyFavoriteRequestFactory(
            $this->requestFactory,
            $this->ensureStreamFactory('destroyFavorite'),
            $this->config,
            $id,
        );
        $response = $this->httpClient->sendRequest($factory->create());

        if ($response->getStatusCode() === 200) {
            return Tweet::fromApiResponse(json_decode((string) $response->getBody()));
        }

        throw $this->createException($response);
    }

    private function parseTweetList(ResponseInterface $response): TweetList
    {
        $data = json_decode((string) $response->getBody());
        $tweets = [];
        if (is_array($data)) {
            foreach ($data as $tweetData) {
                $tweets[] = Tweet::fromApiResponse($tweetData);
            }
        }

        return new TweetList($tweets);
    }

    private function ensureStreamFactory(string $methodName): StreamFactoryInterface
    {
        if ($this->streamFactory === null) {
            throw new TwitterApiException(
                "StreamFactoryInterface is required for {$methodName}. Provide it in the constructor.",
            );
        }

        return $this->streamFactory;
    }

    private function createException(ResponseInterface $response): TwitterApiException
    {
        $body = (string) $response->getBody();

        return new TwitterApiException(
            $this->parseErrorResponse($response),
            $response->getStatusCode(),
            $body,
        );
    }

    private function parseErrorResponse(ResponseInterface $response): string
    {
        $statusCode = $response->getStatusCode();
        $reasonPhrase = $response->getReasonPhrase();
        $baseMessage = "{$statusCode} {$reasonPhrase}";

        try {
            $body = (string) $response->getBody();
            $errorData = json_decode($body);

            if (!$errorData) {
                return $baseMessage;
            }

            $details = [];

            if (isset($errorData->errors) && is_array($errorData->errors)) {
                foreach ($errorData->errors as $error) {
                    if (is_object($error) && isset($error->message)) {
                        $details[] = $error->message;
                    } elseif (is_string($error)) {
                        $details[] = $error;
                    }
                }
            }

            if (empty($details) && isset($errorData->error) && is_string($errorData->error)) {
                $details[] = $errorData->error;
            }

            if (!empty($details)) {
                return $baseMessage . ': ' . implode('; ', $details);
            }

            return $baseMessage;
        } catch (Throwable) {
            return $baseMessage;
        }
    }
}
