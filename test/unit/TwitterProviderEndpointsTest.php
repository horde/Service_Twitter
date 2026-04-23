<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V1\Test\Unit;

use Horde\OAuth\V10a\Client\ProviderEndpoints;
use Horde\Service\Twitter\V1\TwitterProviderEndpoints;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TwitterProviderEndpoints::class)]
final class TwitterProviderEndpointsTest extends TestCase
{
    public function testCreateReturnsProviderEndpoints(): void
    {
        $endpoints = TwitterProviderEndpoints::create();

        self::assertInstanceOf(ProviderEndpoints::class, $endpoints);
        self::assertSame('https://api.twitter.com/oauth/request_token', $endpoints->requestTokenUrl);
        self::assertSame('https://api.twitter.com/oauth/authorize', $endpoints->authorizeUrl);
        self::assertSame('https://api.twitter.com/oauth/access_token', $endpoints->accessTokenUrl);
    }
}
