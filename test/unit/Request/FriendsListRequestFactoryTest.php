<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V1\Test\Unit\Request;

use Horde\Service\Twitter\V1\CursorParams;
use Horde\Service\Twitter\V1\Request\FriendsListRequestFactory;
use Horde\Service\Twitter\V1\TwitterApiConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;

#[CoversClass(FriendsListRequestFactory::class)]
final class FriendsListRequestFactoryTest extends TestCase
{
    public function testCreatesGetRequestToCorrectEndpoint(): void
    {
        $config = new TwitterApiConfig();
        $params = new CursorParams();
        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = VerifyCredentialsRequestFactoryTest::createTrackingRequest();

        $psrRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('GET', $this->stringEndsWith('/friends/list.json'))
            ->willReturn($request);

        $factory = new FriendsListRequestFactory($psrRequestFactory, $config, $params);
        $factory->create();
    }

    public function testAppendsQueryParams(): void
    {
        $config = new TwitterApiConfig();
        $params = new CursorParams(screenName: 'alice', cursor: 555);
        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = VerifyCredentialsRequestFactoryTest::createTrackingRequest();

        $psrRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('GET', $this->logicalAnd(
                $this->stringContains('screen_name=alice'),
                $this->stringContains('cursor=555'),
            ))
            ->willReturn($request);

        $factory = new FriendsListRequestFactory($psrRequestFactory, $config, $params);
        $factory->create();
    }
}
