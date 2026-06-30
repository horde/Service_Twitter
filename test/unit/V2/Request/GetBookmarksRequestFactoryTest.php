<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit\Request;

use Horde\Service\Twitter\V1\Test\Unit\Request\VerifyCredentialsRequestFactoryTest;
use Horde\Service\Twitter\V2\Request\GetBookmarksRequestFactory;
use Horde\Service\Twitter\V2\TwitterApiConfig;
use Horde\Service\Twitter\V2\UserTimelineParams;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;

#[CoversClass(GetBookmarksRequestFactory::class)]
final class GetBookmarksRequestFactoryTest extends TestCase
{
    public function testCreatesGetRequestToBookmarksEndpoint(): void
    {
        $config = new TwitterApiConfig();
        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = VerifyCredentialsRequestFactoryTest::createTrackingRequest();

        $psrRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('GET', $this->stringContains('/2/users/42/bookmarks'))
            ->willReturn($request);

        $factory = new GetBookmarksRequestFactory($psrRequestFactory, $config, '42');
        $factory->create();
    }

    public function testAppendsQueryParams(): void
    {
        $config = new TwitterApiConfig();
        $params = new UserTimelineParams(maxResults: 5);
        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = VerifyCredentialsRequestFactoryTest::createTrackingRequest();

        $psrRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('GET', $this->logicalAnd(
                $this->stringContains('/2/users/42/bookmarks'),
                $this->stringContains('max_results=5'),
            ))
            ->willReturn($request);

        $factory = new GetBookmarksRequestFactory($psrRequestFactory, $config, '42', $params);
        $factory->create();
    }
}
