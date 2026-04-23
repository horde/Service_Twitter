<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V1\Test\Integration;

use Horde\Service\Twitter\V1\TwitterApiClient;
use Horde\Service\Twitter\V1\TwitterApiConfig;
use Horde\Service\Twitter\V1\TwitterUser;
use Horde\Service\Twitter\V1\RateLimitStatus;
use Horde\Service\Twitter\V1\Request\RateLimitStatusRequestFactory;
use Horde\Service\Twitter\V1\Request\VerifyCredentialsRequestFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(TwitterApiClient::class)]
#[CoversClass(VerifyCredentialsRequestFactory::class)]
#[CoversClass(RateLimitStatusRequestFactory::class)]
#[CoversClass(TwitterUser::class)]
#[CoversClass(RateLimitStatus::class)]
final class TwitterApiClientAccountTest extends TestCase
{
    public function testVerifyCredentialsReturnsTwitterUser(): void
    {
        $json = json_encode([
            'id' => 42,
            'name' => 'Test User',
            'screen_name' => 'testuser',
            'description' => 'A test account',
            'profile_image_url_https' => 'https://pbs.twimg.com/img.jpg',
            'followers_count' => 100,
            'friends_count' => 50,
            'statuses_count' => 200,
            'created_at' => 'Mon Jan 01 00:00:00 +0000 2020',
            'verified' => true,
            'protected' => false,
        ]);

        $client = $this->buildClient(200, $json);
        $user = $client->verifyCredentials();

        self::assertSame(42, $user->id);
        self::assertSame('testuser', $user->screenName);
        self::assertSame('Test User', $user->name);
        self::assertTrue($user->verified);
    }

    public function testGetRateLimitStatusReturnsRateLimitStatus(): void
    {
        $json = json_encode([
            'rate_limit_context' => ['access_token' => 'abc'],
            'resources' => [
                'statuses' => [
                    '/statuses/home_timeline' => [
                        'limit' => 15,
                        'remaining' => 10,
                        'reset' => 1700000000,
                    ],
                ],
            ],
        ]);

        $client = $this->buildClient(200, $json);
        $status = $client->getRateLimitStatus();

        self::assertSame('abc', $status->context);
        self::assertCount(1, $status->resources);
        self::assertArrayHasKey('/statuses/home_timeline', $status->resources);
    }

    private function buildClient(int $statusCode, string $body): TwitterApiClient
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->atLeastOnce())->method('__toString')->willReturn($body);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('getStatusCode')->willReturn($statusCode);
        $response->expects($this->atLeastOnce())->method('getBody')->willReturn($stream);

        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response);

        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->atLeastOnce())->method('withHeader')->willReturnSelf();

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects($this->once())->method('createRequest')->willReturn($request);

        return new TwitterApiClient(
            $httpClient,
            $requestFactory,
            new TwitterApiConfig(),
        );
    }
}
