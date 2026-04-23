<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V1\Test\Unit\Request;

use Horde\Service\Twitter\V1\Request\UpdateStatusRequestFactory;
use Horde\Service\Twitter\V1\TwitterApiConfig;
use Horde\Service\Twitter\V1\UpdateStatusParams;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(UpdateStatusRequestFactory::class)]
final class UpdateStatusRequestFactoryTest extends TestCase
{
    public function testCreatesPostRequestToUpdateEndpoint(): void
    {
        $config = new TwitterApiConfig();
        $params = new UpdateStatusParams(status: 'Hello');
        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->never())->method($this->anything());
        $request = VerifyCredentialsRequestFactoryTest::createTrackingRequest();

        $psrRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('POST', $this->stringEndsWith('/statuses/update.json'))
            ->willReturn($request);

        $streamFactory->expects($this->once())
            ->method('createStream')
            ->willReturn($stream);

        $factory = new UpdateStatusRequestFactory($psrRequestFactory, $streamFactory, $config, $params);
        $factory->create();
    }

    public function testStreamFactoryReceivesFormEncodedBody(): void
    {
        $config = new TwitterApiConfig();
        $params = new UpdateStatusParams(status: 'Hello world');
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
            ->with($this->stringContains('status=Hello'))
            ->willReturn($stream);

        $factory = new UpdateStatusRequestFactory($psrRequestFactory, $streamFactory, $config, $params);
        $factory->create();
    }

    public function testSetsFormContentType(): void
    {
        $config = new TwitterApiConfig();
        $params = new UpdateStatusParams(status: 'Test');
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
            ->willReturn($stream);

        $factory = new UpdateStatusRequestFactory($psrRequestFactory, $streamFactory, $config, $params);
        $result = $factory->create();

        self::assertSame('application/x-www-form-urlencoded', $result->getHeaderLine('Content-Type'));
    }
}
