<?php

declare(strict_types=1);

/**
 * Browser-based OAuth 2.0 PKCE flow for Twitter/X using the V2 PSR-18 API.
 *
 * This script handles the full OAuth 2.0 Authorization Code + PKCE flow:
 *   1. Generate PKCE verifier/challenge and redirect the user to Twitter
 *   2. Handle the callback with the authorization code to obtain tokens
 *   3. Use the access token to make authenticated API calls
 *
 * Copyright 2009-2026 Horde LLC (http://www.horde.org/)
 *
 * @author   Michael J. Rubinsky <mrubinsk@horde.org>
 * @license  http://www.horde.org/licenses/bsd BSD
 * @package  Service_Twitter
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Horde\OAuth\Client\AuthenticatedHttpClient;
use Horde\OAuth\Client\OAuth2Client;
use Horde\OAuth\Client\PkceGenerator;
use Horde\OAuth\Client\TokenSet;
use Horde\Service\Twitter\V2\TwitterApiClient;
use Horde\Service\Twitter\V2\TwitterApiException;
use Horde\Service\Twitter\V2\TwitterProviderEndpoints;

session_start();

/*
 * App credentials — obtained at https://developer.twitter.com
 */
$clientId     = '*****';
$clientSecret = null; // Public clients (PKCE) may not have a secret

/*
 * The URL of this script — Twitter redirects back here after authorization.
 */
$callbackUrl = 'http://localhost/doc/examples/V2/twitter_browser.php';

/*
 * Scopes required for the Horde/Twitter integration.
 */
$scopes = ['tweet.read', 'tweet.write', 'users.read', 'offline.access'];

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

$providerConfig = TwitterProviderEndpoints::create();

$oauthClient = new OAuth2Client(
    $providerConfig,
    $clientId,
    $clientSecret,
    $callbackUrl,
    $httpClient,
    $requestFactory,
    $streamFactory,
);

/*
 * Phase 3: We already have an access token — make API calls.
 */
if (!empty($_SESSION['twitter_v2_access_token'])) {
    $tokenSet = new TokenSet(
        accessToken: $_SESSION['twitter_v2_access_token'],
        tokenType: 'bearer',
        refreshToken: $_SESSION['twitter_v2_refresh_token'] ?? null,
    );

    $authenticatedClient = new AuthenticatedHttpClient($httpClient, $tokenSet);

    $twitter = TwitterApiClient::create(
        $authenticatedClient,
        $requestFactory,
        $streamFactory,
    );

    try {
        $user = $twitter->getMe();
        echo "Authenticated as {$user} ({$user->name})\n";
        if ($user->description !== null) {
            echo "Bio: {$user->description}\n";
        }
    } catch (TwitterApiException $e) {
        echo "API error: {$e->getMessage()}\n";
    }

    exit;
}

/*
 * Phase 2: Returning from Twitter with authorization code — exchange for tokens.
 */
if (
    !empty($_GET['code'])
    && !empty($_SESSION['twitter_v2_state'])
    && !empty($_SESSION['twitter_v2_pkce_verifier'])
    && ($_GET['state'] ?? '') === $_SESSION['twitter_v2_state']
) {
    $codeVerifier = $_SESSION['twitter_v2_pkce_verifier'];

    unset(
        $_SESSION['twitter_v2_state'],
        $_SESSION['twitter_v2_pkce_verifier'],
    );

    try {
        $tokenSet = $oauthClient->exchangeCode($_GET['code'], $codeVerifier);

        $_SESSION['twitter_v2_access_token']  = $tokenSet->accessToken;
        $_SESSION['twitter_v2_refresh_token'] = $tokenSet->refreshToken;

        header('Location: ' . $callbackUrl);
        exit;
    } catch (\Throwable $e) {
        die('Failed to exchange authorization code: ' . $e->getMessage());
    }
}

/*
 * Phase 1: No tokens — generate PKCE challenge and redirect to Twitter.
 */
$state        = bin2hex(random_bytes(16));
$codeVerifier = PkceGenerator::generateVerifier();
$codeChallenge = PkceGenerator::computeChallenge($codeVerifier);

$_SESSION['twitter_v2_state']         = $state;
$_SESSION['twitter_v2_pkce_verifier'] = $codeVerifier;

$authUrl = $oauthClient->getAuthorizationUrl(
    scopes: $scopes,
    state: $state,
    codeChallenge: $codeChallenge,
    codeChallengeMethod: 'S256',
);

header('Location: ' . $authUrl);
exit;
