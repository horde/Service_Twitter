<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V1\Test\Unit\Request;

use Horde\Service\Twitter\V1\Request\CreateFavoriteRequestFactory;
use Horde\Service\Twitter\V1\TwitterApiConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(CreateFavoriteRequestFactory::class)]
final class CreateFavoriteRequestFactoryTest extends TestCase
{
    public function testCreatesPostRequest(): void
    {
        $config = new TwitterApiConfig();
        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->never())->method($this->anything());
        $request = VerifyCredentialsRequestFactoryTest::createTrackingRequest();

        $psrRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('POST', $this->stringEndsWith('/favorites/create.json'))
            ->willReturn($request);

        $streamFactory->expects($this->once())
            ->method('createStream')
            ->willReturn($stream);

        $factory = new CreateFavoriteRequestFactory($psrRequestFactory, $streamFactory, $config, 42);
        $factory->create();
    }

    public function testStreamFactoryReceivesIdInBody(): void
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
            ->with($this->stringContains('id=42'))
            ->willReturn($stream);

        $factory = new CreateFavoriteRequestFactory($psrRequestFactory, $streamFactory, $config, 42);
        $factory->create();
    }
}
