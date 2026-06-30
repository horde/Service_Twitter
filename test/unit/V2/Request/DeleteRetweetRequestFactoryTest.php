<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit\Request;

use Horde\Service\Twitter\V1\Test\Unit\Request\VerifyCredentialsRequestFactoryTest;
use Horde\Service\Twitter\V2\Request\DeleteRetweetRequestFactory;
use Horde\Service\Twitter\V2\TwitterApiConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;

#[CoversClass(DeleteRetweetRequestFactory::class)]
final class DeleteRetweetRequestFactoryTest extends TestCase
{
    public function testCreatesDeleteRequestWithUserAndTweetIdInPath(): void
    {
        $config = new TwitterApiConfig();
        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = VerifyCredentialsRequestFactoryTest::createTrackingRequest();

        $psrRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('DELETE', $this->stringContains('/2/users/42/retweets/999'))
            ->willReturn($request);

        $factory = new DeleteRetweetRequestFactory($psrRequestFactory, $config, '42', '999');
        $factory->create();
    }
}
