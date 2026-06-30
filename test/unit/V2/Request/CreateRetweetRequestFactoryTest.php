<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit\Request;

use Horde\Service\Twitter\V1\Test\Unit\Request\VerifyCredentialsRequestFactoryTest;
use Horde\Service\Twitter\V2\Request\CreateRetweetRequestFactory;
use Horde\Service\Twitter\V2\TwitterApiConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(CreateRetweetRequestFactory::class)]
final class CreateRetweetRequestFactoryTest extends TestCase
{
    public function testCreatesPostRequestWithUserIdInPath(): void
    {
        $config = new TwitterApiConfig();
        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->never())->method($this->anything());
        $request = VerifyCredentialsRequestFactoryTest::createTrackingRequest();

        $psrRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('POST', $this->stringContains('/2/users/42/retweets'))
            ->willReturn($request);

        $streamFactory->expects($this->once())
            ->method('createStream')
            ->with($this->stringContains('"tweet_id":"999"'))
            ->willReturn($stream);

        $factory = new CreateRetweetRequestFactory($psrRequestFactory, $streamFactory, $config, '42', '999');
        $factory->create();
    }
}
