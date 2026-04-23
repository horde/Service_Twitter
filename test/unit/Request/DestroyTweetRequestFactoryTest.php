<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V1\Test\Unit\Request;

use Horde\Service\Twitter\V1\Request\DestroyTweetRequestFactory;
use Horde\Service\Twitter\V1\TwitterApiConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(DestroyTweetRequestFactory::class)]
final class DestroyTweetRequestFactoryTest extends TestCase
{
    public function testCreatesPostRequestWithIdInPath(): void
    {
        $config = new TwitterApiConfig();
        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->never())->method($this->anything());
        $request = VerifyCredentialsRequestFactoryTest::createTrackingRequest();

        $psrRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('POST', $this->stringContains('/statuses/destroy/12345.json'))
            ->willReturn($request);

        $streamFactory->expects($this->once())
            ->method('createStream')
            ->with('')
            ->willReturn($stream);

        $factory = new DestroyTweetRequestFactory($psrRequestFactory, $streamFactory, $config, 12345);
        $factory->create();
    }

    public function testStreamFactoryCalledWithEmptyBody(): void
    {
        $config = new TwitterApiConfig();
        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->never())->method($this->anything());
        $request = VerifyCredentialsRequestFactoryTest::createTrackingRequest();

        $psrRequestFactory->expects($this->once())
            ->method('createRequest')
            ->willReturn($request);

        $streamFactory->expects($this->once())
            ->method('createStream')
            ->with($this->identicalTo(''))
            ->willReturn($stream);

        $factory = new DestroyTweetRequestFactory($psrRequestFactory, $streamFactory, $config, 999);
        $factory->create();
    }
}
