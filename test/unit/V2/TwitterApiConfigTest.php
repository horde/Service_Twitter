<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit;

use Horde\Service\Twitter\V2\TwitterApiConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TwitterApiConfig::class)]
final class TwitterApiConfigTest extends TestCase
{
    public function testDefaultBaseUrl(): void
    {
        $config = new TwitterApiConfig();

        self::assertSame('https://api.twitter.com', $config->baseUrl);
    }

    public function testCustomBaseUrl(): void
    {
        $config = new TwitterApiConfig(baseUrl: 'https://custom.example.com');

        self::assertSame('https://custom.example.com', $config->baseUrl);
    }
}
