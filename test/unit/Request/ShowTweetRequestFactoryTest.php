<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V1\Test\Unit\Request;

use Horde\Service\Twitter\V1\Request\ShowTweetRequestFactory;
use Horde\Service\Twitter\V1\TwitterApiConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;

#[CoversClass(ShowTweetRequestFactory::class)]
final class ShowTweetRequestFactoryTest extends TestCase
{
    public function testCreatesGetRequestWithIdInQuery(): void
    {
        $config = new TwitterApiConfig();
        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = VerifyCredentialsRequestFactoryTest::createTrackingRequest();

        $psrRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('GET', $this->stringContains('/statuses/show.json?id=12345'))
            ->willReturn($request);

        $factory = new ShowTweetRequestFactory($psrRequestFactory, $config, 12345);
        $factory->create();
    }
}
