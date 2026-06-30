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

use Closure;
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
            return $this->decodeTweetPage(
                $response,
                fn (string $next): PaginatedResponse => $this->getUserTimeline(
                    $userId,
                    self::withPaginationToken($params, $next),
                    $fields,
                ),
            );
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
            return $this->decodeTweetPage(
                $response,
                fn (string $next): PaginatedResponse => $this->getBookmarks(
                    $userId,
                    self::withPaginationToken($params, $next),
                    $fields,
                ),
            );
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

    /**
     * Decode a tweet listing response (timeline / bookmarks) into a
     * PaginatedResponse, wiring the supplied fetcher closure so callers can
     * walk every page via `iterator()` without managing pagination tokens.
     *
     * @param Closure(string): PaginatedResponse<Tweet> $fetcher
     * @return PaginatedResponse<Tweet>
     */
    private function decodeTweetPage(ResponseInterface $response, Closure $fetcher): PaginatedResponse
    {
        $json = json_decode((string) $response->getBody());
        $tweets = array_map(
            Tweet::fromApiResponse(...),
            $json->data ?? [],
        );
        $meta = PaginationMeta::fromApiResponse($json->meta ?? (object) []);
        $includes = null;
        if (isset($json->includes) && is_object($json->includes)) {
            $includes = Includes::fromApiResponse($json->includes);
        }

        return new PaginatedResponse($tweets, $meta, $includes, $fetcher);
    }

    /**
     * Produce a new UserTimelineParams identical to $base but with
     * paginationToken replaced. Used by the auto-pagination fetcher closure
     * to walk to the next page without mutating the caller's input.
     */
    private static function withPaginationToken(?UserTimelineParams $base, string $token): UserTimelineParams
    {
        if ($base === null) {
            return new UserTimelineParams(paginationToken: $token);
        }

        return new UserTimelineParams(
            maxResults: $base->maxResults,
            paginationToken: $token,
            sinceId: $base->sinceId,
            untilId: $base->untilId,
            exclude: $base->exclude,
            startTime: $base->startTime,
            endTime: $base->endTime,
        );
    }

    private function createException(ResponseInterface $response): TwitterApiException
    {
        $message = $this->parseErrorResponse($response);

        if ($response->getStatusCode() === 429) {
            return RateLimitException::fromResponse($response, $message);
        }

        return new TwitterApiException(
            $message,
            $response->getStatusCode(),
            (string) $response->getBody(),
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
