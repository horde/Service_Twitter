<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V1\Test\Unit\Request;

use Horde\Service\Twitter\V1\Request\RateLimitStatusRequestFactory;
use Horde\Service\Twitter\V1\TwitterApiConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;

#[CoversClass(RateLimitStatusRequestFactory::class)]
final class RateLimitStatusRequestFactoryTest extends TestCase
{
    public function testCreatesGetRequestToApplicationEndpoint(): void
    {
        $config = new TwitterApiConfig();
        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = VerifyCredentialsRequestFactoryTest::createTrackingRequest();

        $psrRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('GET', $this->stringContains('/application/rate_limit_status.json'))
            ->willReturn($request);

        $factory = new RateLimitStatusRequestFactory($psrRequestFactory, $config);
        $factory->create();
    }
}
