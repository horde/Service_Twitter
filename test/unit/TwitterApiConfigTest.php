<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V1\Test\Unit;

use Horde\Service\Twitter\V1\TwitterApiConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TwitterApiConfig::class)]
final class TwitterApiConfigTest extends TestCase
{
    public function testDefaultBaseUrl(): void
    {
        $config = new TwitterApiConfig();

        self::assertSame('https://api.twitter.com/1.1', $config->baseUrl);
    }

    public function testCustomBaseUrl(): void
    {
        $config = new TwitterApiConfig(baseUrl: 'https://custom.api.example.com/v1');

        self::assertSame('https://custom.api.example.com/v1', $config->baseUrl);
    }
}
