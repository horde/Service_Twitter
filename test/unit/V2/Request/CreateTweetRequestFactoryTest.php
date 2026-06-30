<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit\Request;

use Horde\Service\Twitter\V1\Test\Unit\Request\VerifyCredentialsRequestFactoryTest;
use Horde\Service\Twitter\V2\CreateTweetParams;
use Horde\Service\Twitter\V2\Request\CreateTweetRequestFactory;
use Horde\Service\Twitter\V2\TwitterApiConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(CreateTweetRequestFactory::class)]
final class CreateTweetRequestFactoryTest extends TestCase
{
    public function testCreatesPostRequestToTweetsEndpoint(): void
    {
        $config = new TwitterApiConfig();
        $params = new CreateTweetParams(text: 'Hello');
        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->never())->method($this->anything());
        $request = VerifyCredentialsRequestFactoryTest::createTrackingRequest();

        $psrRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('POST', $this->stringEndsWith('/2/tweets'))
            ->willReturn($request);

        $streamFactory->expects($this->once())
            ->method('createStream')
            ->with($this->stringContains('"text":"Hello"'))
            ->willReturn($stream);

        $factory = new CreateTweetRequestFactory($psrRequestFactory, $streamFactory, $config, $params);
        $result = $factory->create();

        self::assertSame('application/json', $result->getHeaderLine('Content-Type'));
        self::assertSame('application/json', $result->getHeaderLine('Accept'));
    }
}
