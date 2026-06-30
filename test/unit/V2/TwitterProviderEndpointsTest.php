<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit;

use Horde\Service\Twitter\V2\TwitterProviderEndpoints;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TwitterProviderEndpoints::class)]
final class TwitterProviderEndpointsTest extends TestCase
{
    public function testCreateReturnsProviderConfig(): void
    {
        $config = TwitterProviderEndpoints::create();

        self::assertSame('https://twitter.com', $config->issuer);
        self::assertSame('https://twitter.com/i/oauth2/authorize', $config->authorizationEndpoint);
        self::assertSame('https://api.twitter.com/2/oauth2/token', $config->tokenEndpoint);
        self::assertSame('https://api.twitter.com/2/oauth2/revoke', $config->revocationEndpoint);
    }

    public function testScopesSupportedIncludesExpectedScopes(): void
    {
        $config = TwitterProviderEndpoints::create();

        self::assertContains('tweet.read', $config->scopesSupported);
        self::assertContains('tweet.write', $config->scopesSupported);
        self::assertContains('users.read', $config->scopesSupported);
        self::assertContains('offline.access', $config->scopesSupported);
        self::assertContains('bookmark.read', $config->scopesSupported);
        self::assertContains('bookmark.write', $config->scopesSupported);
        self::assertContains('like.read', $config->scopesSupported);
        self::assertContains('like.write', $config->scopesSupported);
        self::assertCount(8, $config->scopesSupported);
    }
}
