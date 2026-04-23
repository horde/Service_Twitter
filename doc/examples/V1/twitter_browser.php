<?php

declare(strict_types=1);

/**
 * Browser-based 3-legged OAuth flow for Twitter using the V1 PSR-18 API.
 *
 * This script handles the full OAuth 1.0a dance:
 *   1. Obtain a request token and redirect the user to Twitter for authorization
 *   2. Handle the callback with the oauth_verifier to obtain an access token
 *   3. Use the access token to make authenticated API calls
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
use Horde\OAuth\V10a\Client\OAuth1Client;
use Horde\OAuth\V10a\Client\Token;
use Horde\OAuth\V10a\Signature\HmacSha1;
use Horde\Service\Twitter\V1\TwitterApiClient;
use Horde\Service\Twitter\V1\TwitterApiException;
use Horde\Service\Twitter\V1\TwitterProviderEndpoints;

session_start();

/*
 * Keys — obtained when registering your app at https://developer.twitter.com
 */
$consumerKey    = '*****';
$consumerSecret = '*****';

/*
 * The URL of this script — Twitter redirects back here after authorization.
 */
$callbackUrl = 'http://localhost/examples/V1/twitter_browser.php';

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
    die('Please configure a PSR-18 HTTP client and PSR-17 factories.');
}

$consumer       = new ConsumerCredentials($consumerKey, $consumerSecret);
$signatureMethod = new HmacSha1();
$endpoints      = TwitterProviderEndpoints::create();

/*
 * The OAuth1Client handles the token exchange flow.
 */
$oauthClient = new OAuth1Client(
    $consumer,
    $endpoints,
    $signatureMethod,
    $httpClient,
    $requestFactory,
    $streamFactory,
);

/*
 * Phase 3: We already have an access token — make API calls.
 */
if (!empty($_SESSION['twitter_access_key']) && !empty($_SESSION['twitter_access_secret'])) {
    $accessToken = new Token(
        $_SESSION['twitter_access_key'],
        $_SESSION['twitter_access_secret'],
    );

    $authenticatedClient = new AuthenticatedHttpClient(
        $httpClient,
        $consumer,
        $accessToken,
        $signatureMethod,
    );

    $twitter = TwitterApiClient::create(
        $authenticatedClient,
        $requestFactory,
        $streamFactory,
    );

    try {
        $user = $twitter->verifyCredentials();
        echo "Authenticated as @{$user->screenName} ({$user->name})\n";
        echo "Followers: {$user->followersCount}, Following: {$user->friendsCount}\n";
    } catch (TwitterApiException $e) {
        echo "API error: {$e->getMessage()}\n";
    }

    exit;
}

/*
 * Phase 2: Returning from Twitter with oauth_verifier — exchange for access token.
 */
if (
    !empty($_GET['oauth_verifier'])
    && !empty($_SESSION['twitter_request_key'])
    && !empty($_SESSION['twitter_request_secret'])
) {
    $requestToken = new Token(
        $_SESSION['twitter_request_key'],
        $_SESSION['twitter_request_secret'],
    );

    unset($_SESSION['twitter_request_key'], $_SESSION['twitter_request_secret']);

    try {
        $accessToken = $oauthClient->getAccessToken(
            $requestToken,
            $_GET['oauth_verifier'],
        );

        $_SESSION['twitter_access_key']    = $accessToken->key;
        $_SESSION['twitter_access_secret'] = $accessToken->secret;

        header('Location: ' . $callbackUrl);
        exit;
    } catch (\Throwable $e) {
        die('Failed to obtain access token: ' . $e->getMessage());
    }
}

/*
 * Phase 1: No tokens at all — obtain a request token and redirect to Twitter.
 */
try {
    $requestToken = $oauthClient->getRequestToken($callbackUrl);

    $_SESSION['twitter_request_key']    = $requestToken->key;
    $_SESSION['twitter_request_secret'] = $requestToken->secret;

    $authUrl = $oauthClient->getAuthorizationUrl($requestToken, $callbackUrl);

    header('Location: ' . $authUrl);
    exit;
} catch (\Throwable $e) {
    die('Failed to obtain request token: ' . $e->getMessage());
}
