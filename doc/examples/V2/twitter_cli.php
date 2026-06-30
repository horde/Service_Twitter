#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Simple Twitter/X CLI client using the V2 PSR-18 API with OAuth 2.0.
 *
 * Requires a pre-existing access token (obtained via the OAuth 2.0 PKCE flow).
 * See twitter_browser.php for the full authorization flow.
 *
 * Copyright 2009-2026 Horde LLC (http://www.horde.org/)
 *
 * @author   Michael J. Rubinsky <mrubinsk@horde.org>
 * @license  http://www.horde.org/licenses/bsd BSD
 * @package  Service_Twitter
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Horde\OAuth\Client\AuthenticatedHttpClient;
use Horde\OAuth\Client\TokenSet;
use Horde\Service\Twitter\V2\CreateTweetParams;
use Horde\Service\Twitter\V2\TwitterApiClient;
use Horde\Service\Twitter\V2\TwitterApiException;
use Horde\Service\Twitter\V2\UserTimelineParams;

/*
 * Tokens — obtained via the OAuth 2.0 PKCE flow (see twitter_browser.php).
 */
$accessToken  = '*****';
$refreshToken = '*****';

/*
 * PSR-18 HTTP client and PSR-17 factories.
 * Use any implementation — Guzzle, Buzz, Symfony HttpClient, etc.
 *
 * Example with Guzzle:
 *   $httpClient     = new \GuzzleHttp\Client();
 *   $requestFactory = new \GuzzleHttp\Psr7\HttpFactory();
 *   $streamFactory  = new \GuzzleHttp\Psr7\HttpFactory();
 */
$httpClient     = /* your PSR-18 ClientInterface */ null;
$requestFactory = /* your PSR-17 RequestFactoryInterface */ null;
$streamFactory  = /* your PSR-17 StreamFactoryInterface */ null;

if ($httpClient === null || $requestFactory === null || $streamFactory === null) {
    fwrite(STDERR, "Please configure a PSR-18 HTTP client and PSR-17 factories.\n");
    exit(1);
}

/*
 * Wrap the HTTP client with OAuth 2.0 Bearer token injection.
 * On 401, the client will automatically refresh the token if a TokenRefresher is provided.
 */
$tokenSet = new TokenSet(
    accessToken: $accessToken,
    tokenType: 'bearer',
    refreshToken: $refreshToken,
);

$authenticatedClient = new AuthenticatedHttpClient($httpClient, $tokenSet);

/*
 * Create the Twitter API V2 client.
 * The authenticated client handles authorization — TwitterApiClient never sees tokens.
 */
$twitter = TwitterApiClient::create(
    $authenticatedClient,
    $requestFactory,
    $streamFactory,
);

/*
 * Get the authenticated user.
 */
try {
    $me = $twitter->getMe();
    echo "Authenticated as {$me} ({$me->name})\n";
} catch (TwitterApiException $e) {
    fwrite(STDERR, "Error verifying credentials: {$e->getMessage()}\n");
    exit(1);
}

/*
 * Post a tweet.
 */
try {
    $tweet = $twitter->createTweet(
        new CreateTweetParams(text: 'Testing Horde/Twitter V2 integration'),
    );
    echo "Posted tweet {$tweet->id}: {$tweet->text}\n";
} catch (TwitterApiException $e) {
    fwrite(STDERR, "Error posting tweet: {$e->getMessage()}\n");
}

/*
 * Read the user's timeline.
 */
try {
    $response = $twitter->getUserTimeline(
        $me->id,
        new UserTimelineParams(maxResults: 5),
    );
    echo "\nTimeline ({$response->meta->resultCount} tweets):\n";
    foreach ($response->data as $tweet) {
        echo "  [{$tweet->id}] {$tweet->text}\n";
    }
    if ($response->meta->hasNextPage()) {
        echo "  (more tweets available, next_token: {$response->meta->nextToken})\n";
    }
} catch (TwitterApiException $e) {
    fwrite(STDERR, "Error reading timeline: {$e->getMessage()}\n");
}
