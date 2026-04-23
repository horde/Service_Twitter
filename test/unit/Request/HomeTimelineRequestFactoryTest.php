<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V1\Test\Unit\Request;

use Horde\Service\Twitter\V1\Request\HomeTimelineRequestFactory;
use Horde\Service\Twitter\V1\TimelineParams;
use Horde\Service\Twitter\V1\TwitterApiConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;

#[CoversClass(HomeTimelineRequestFactory::class)]
final class HomeTimelineRequestFactoryTest extends TestCase
{
    public function testCreatesGetRequestWithDefaultParams(): void
    {
        $config = new TwitterApiConfig();
        $params = new TimelineParams();
        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = VerifyCredentialsRequestFactoryTest::createTrackingRequest();

        $psrRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('GET', $this->stringEndsWith('/statuses/home_timeline.json'))
            ->willReturn($request);

        $factory = new HomeTimelineRequestFactory($psrRequestFactory, $config, $params);
        $factory->create();
    }

    public function testCreatesGetRequestWithParams(): void
    {
        $config = new TwitterApiConfig();
        $params = new TimelineParams(sinceId: 100);
        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = VerifyCredentialsRequestFactoryTest::createTrackingRequest();

        $psrRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('GET', $this->stringContains('since_id=100'))
            ->willReturn($request);

        $factory = new HomeTimelineRequestFactory($psrRequestFactory, $config, $params);
        $factory->create();
    }
}
