<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit\Request;

use Horde\Service\Twitter\V1\Test\Unit\Request\VerifyCredentialsRequestFactoryTest;
use Horde\Service\Twitter\V2\Request\DeleteTweetRequestFactory;
use Horde\Service\Twitter\V2\TwitterApiConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;

#[CoversClass(DeleteTweetRequestFactory::class)]
final class DeleteTweetRequestFactoryTest extends TestCase
{
    public function testCreatesDeleteRequestWithIdInPath(): void
    {
        $config = new TwitterApiConfig();
        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = VerifyCredentialsRequestFactoryTest::createTrackingRequest();

        $psrRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('DELETE', $this->stringEndsWith('/2/tweets/123'))
            ->willReturn($request);

        $factory = new DeleteTweetRequestFactory($psrRequestFactory, $config, '123');
        $result = $factory->create();

        self::assertSame('application/json', $result->getHeaderLine('Accept'));
    }
}
