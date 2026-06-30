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

use Horde\Service\Twitter\V2\Request\CreateBookmarkRequestFactory;
use Horde\Service\Twitter\V2\Request\CreateLikeRequestFactory;
use Horde\Service\Twitter\V2\Request\CreateRetweetRequestFactory;
use Horde\Service\Twitter\V2\Request\CreateTweetRequestFactory;
use Horde\Service\Twitter\V2\Request\DeleteLikeRequestFactory;
use Horde\Service\Twitter\V2\Request\DeleteRetweetRequestFactory;
use Horde\Service\Twitter\V2\Request\DeleteTweetRequestFactory;
use Horde\Service\Twitter\V2\Request\GetBookmarksRequestFactory;
use Horde\Service\Twitter\V2\Request\GetTweetRequestFactory;
use Horde\Service\Twitter\V2\Request\GetUserMeRequestFactory;
use Horde\Service\Twitter\V2\Request\GetUserTimelineRequestFactory;
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
        string $baseUrl = 'https://api.twitter.com',
    ): self {
        return new self(
            $httpClient,
            $requestFactory,
            new TwitterApiConfig(baseUrl: $baseUrl),
            $streamFactory,
        );
    }

    public function getMe(?TweetFieldsParams $fields = null): User
    {
        $factory = new GetUserMeRequestFactory($this->requestFactory, $this->config, $fields);
        $response = $this->httpClient->sendRequest($factory->create());

        if ($response->getStatusCode() === 200) {
            $json = json_decode((string) $response->getBody());
            return User::fromApiResponse($json->data);
        }

        throw $this->createException($response);
    }

    public function getTweet(string $tweetId, ?TweetFieldsParams $fields = null): Tweet
    {
        $factory = new GetTweetRequestFactory($this->requestFactory, $this->config, $tweetId, $fields);
        $response = $this->httpClient->sendRequest($factory->create());

        if ($response->getStatusCode() === 200) {
            $json = json_decode((string) $response->getBody());
            return Tweet::fromApiResponse($json->data);
        }

        throw $this->createException($response);
    }

    public function createTweet(CreateTweetParams $params): Tweet
    {
        $factory = new CreateTweetRequestFactory(
            $this->requestFactory,
            $this->ensureStreamFactory('createTweet'),
            $this->config,
            $params,
        );
        $response = $this->httpClient->sendRequest($factory->create());

        if ($response->getStatusCode() === 201) {
            $json = json_decode((string) $response->getBody());
            return Tweet::fromApiResponse($json->data);
        }

        throw $this->createException($response);
    }

    public function deleteTweet(string $tweetId): void
    {
        $factory = new DeleteTweetRequestFactory($this->requestFactory, $this->config, $tweetId);
        $response = $this->httpClient->sendRequest($factory->create());

        if ($response->getStatusCode() === 200) {
            return;
        }

        throw $this->createException($response);
    }

    /** @return PaginatedResponse<Tweet> */
    public function getUserTimeline(
        string $userId,
        ?UserTimelineParams $params = null,
        ?TweetFieldsParams $fields = null,
    ): PaginatedResponse {
        $factory = new GetUserTimelineRequestFactory(
            $this->requestFactory,
            $this->config,
            $userId,
            $params,
            $fields,
        );
        $response = $this->httpClient->sendRequest($factory->create());

        if ($response->getStatusCode() === 200) {
            $json = json_decode((string) $response->getBody());
            $tweets = array_map(
                Tweet::fromApiResponse(...),
                $json->data ?? [],
            );
            $meta = PaginationMeta::fromApiResponse($json->meta ?? (object) []);
            return new PaginatedResponse($tweets, $meta);
        }

        throw $this->createException($response);
    }

    public function retweet(string $userId, string $tweetId): void
    {
        $factory = new CreateRetweetRequestFactory(
            $this->requestFactory,
            $this->ensureStreamFactory('retweet'),
            $this->config,
            $userId,
            $tweetId,
        );
        $response = $this->httpClient->sendRequest($factory->create());

        if ($response->getStatusCode() === 200) {
            return;
        }

        throw $this->createException($response);
    }

    public function undoRetweet(string $userId, string $tweetId): void
    {
        $factory = new DeleteRetweetRequestFactory(
            $this->requestFactory,
            $this->config,
            $userId,
            $tweetId,
        );
        $response = $this->httpClient->sendRequest($factory->create());

        if ($response->getStatusCode() === 200) {
            return;
        }

        throw $this->createException($response);
    }

    public function likeTweet(string $userId, string $tweetId): void
    {
        $factory = new CreateLikeRequestFactory(
            $this->requestFactory,
            $this->ensureStreamFactory('likeTweet'),
            $this->config,
            $userId,
            $tweetId,
        );
        $response = $this->httpClient->sendRequest($factory->create());

        if ($response->getStatusCode() === 200) {
            return;
        }

        throw $this->createException($response);
    }

    public function unlikeTweet(string $userId, string $tweetId): void
    {
        $factory = new DeleteLikeRequestFactory(
            $this->requestFactory,
            $this->config,
            $userId,
            $tweetId,
        );
        $response = $this->httpClient->sendRequest($factory->create());

        if ($response->getStatusCode() === 200) {
            return;
        }

        throw $this->createException($response);
    }

    /** @return PaginatedResponse<Tweet> */
    public function getBookmarks(
        string $userId,
        ?UserTimelineParams $params = null,
        ?TweetFieldsParams $fields = null,
    ): PaginatedResponse {
        $factory = new GetBookmarksRequestFactory(
            $this->requestFactory,
            $this->config,
            $userId,
            $params,
            $fields,
        );
        $response = $this->httpClient->sendRequest($factory->create());

        if ($response->getStatusCode() === 200) {
            $json = json_decode((string) $response->getBody());
            $tweets = array_map(
                Tweet::fromApiResponse(...),
                $json->data ?? [],
            );
            $meta = PaginationMeta::fromApiResponse($json->meta ?? (object) []);
            return new PaginatedResponse($tweets, $meta);
        }

        throw $this->createException($response);
    }

    public function addBookmark(string $userId, string $tweetId): void
    {
        $factory = new CreateBookmarkRequestFactory(
            $this->requestFactory,
            $this->ensureStreamFactory('addBookmark'),
            $this->config,
            $userId,
            $tweetId,
        );
        $response = $this->httpClient->sendRequest($factory->create());

        if ($response->getStatusCode() === 200) {
            return;
        }

        throw $this->createException($response);
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

            if (isset($errorData->detail) && is_string($errorData->detail)) {
                $details[] = $errorData->detail;
            }

            if (isset($errorData->errors) && is_array($errorData->errors)) {
                foreach ($errorData->errors as $error) {
                    if (is_object($error) && isset($error->message)) {
                        $details[] = $error->message;
                    } elseif (is_string($error)) {
                        $details[] = $error;
                    }
                }
            }

            if (empty($details) && isset($errorData->title) && is_string($errorData->title)) {
                $details[] = $errorData->title;
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
