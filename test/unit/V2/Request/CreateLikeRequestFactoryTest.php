<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit\Request;

use Horde\Service\Twitter\V1\Test\Unit\Request\VerifyCredentialsRequestFactoryTest;
use Horde\Service\Twitter\V2\Request\CreateLikeRequestFactory;
use Horde\Service\Twitter\V2\TwitterApiConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(CreateLikeRequestFactory::class)]
final class CreateLikeRequestFactoryTest extends TestCase
{
    public function testCreatesPostRequestToLikesEndpoint(): void
    {
        $config = new TwitterApiConfig();
        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->never())->method($this->anything());
        $request = VerifyCredentialsRequestFactoryTest::createTrackingRequest();

        $psrRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('POST', $this->stringContains('/2/users/42/likes'))
            ->willReturn($request);

        $streamFactory->expects($this->once())
            ->method('createStream')
            ->with($this->stringContains('"tweet_id":"777"'))
            ->willReturn($stream);

        $factory = new CreateLikeRequestFactory($psrRequestFactory, $streamFactory, $config, '42', '777');
        $factory->create();
    }
}
