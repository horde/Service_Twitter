#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Simple Twitter CLI client using the V1 PSR-18 API.
 *
 * Requires a pre-existing access token (obtained via 3-legged OAuth).
 * See twitter_browser.php for the OAuth flow.
 *
 * Copyright 2009-2026 Horde LLC (http://www.horde.org/)
 *
 * @author   Michael J. Rubinsky <mrubinsk@horde.org>
 * @license  http://www.horde.org/licenses/bsd BSD
 * @package  Service_Twitter
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Horde\OAuth\V10a\Client\AuthenticatedHttpClient;
use Horde\OAuth\V10a\Client\ConsumerCredentials;
use Horde\OAuth\V10a\Client\Token;
use Horde\OAuth\V10a\Signature\HmacSha1;
use Horde\Service\Twitter\V1\TimelineParams;
use Horde\Service\Twitter\V1\TwitterApiClient;
use Horde\Service\Twitter\V1\TwitterApiException;
use Horde\Service\Twitter\V1\UpdateStatusParams;

/*
 * Keys — obtained when registering your app at https://developer.twitter.com
 */
$consumerKey    = '*****';
$consumerSecret = '*****';
$accessToken    = '*****-*****';
$accessSecret   = '*****';

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
 * Wrap the HTTP client with OAuth 1.0a signing.
 * Every outgoing request will be signed automatically.
 */
$consumer = new ConsumerCredentials($consumerKey, $consumerSecret);
$token    = new Token($accessToken, $accessSecret);

$authenticatedClient = new AuthenticatedHttpClient(
    $httpClient,
    $consumer,
    $token,
    new HmacSha1(),
);

/*
 * Create the Twitter API client.
 * The authenticated client handles authorization — TwitterApiClient never sees tokens.
 */
$twitter = TwitterApiClient::create(
    $authenticatedClient,
    $requestFactory,
    $streamFactory,
);

/*
 * Post a tweet.
 */
try {
    $tweet = $twitter->updateStatus(
        new UpdateStatusParams(status: 'Testing Horde/Twitter V1 integration'),
    );
    echo "Posted tweet #{$tweet->id}: {$tweet->text}\n";
} catch (TwitterApiException $e) {
    fwrite(STDERR, "Error posting tweet: {$e->getMessage()}\n");
}

/*
 * Read the home timeline.
 */
try {
    $timeline = $twitter->getHomeTimeline(
        new TimelineParams(count: 5),
    );
    echo "\nHome timeline ({$timeline->count()} tweets):\n";
    foreach ($timeline as $tweet) {
        echo "  @{$tweet->user->screenName}: {$tweet->text}\n";
    }
} catch (TwitterApiException $e) {
    fwrite(STDERR, "Error reading timeline: {$e->getMessage()}\n");
}
