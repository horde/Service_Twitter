<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit\Request;

use Horde\Service\Twitter\V1\Test\Unit\Request\VerifyCredentialsRequestFactoryTest;
use Horde\Service\Twitter\V2\Request\GetTweetRequestFactory;
use Horde\Service\Twitter\V2\TweetFieldsParams;
use Horde\Service\Twitter\V2\TwitterApiConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;

#[CoversClass(GetTweetRequestFactory::class)]
final class GetTweetRequestFactoryTest extends TestCase
{
    public function testCreatesGetRequestToCorrectEndpoint(): void
    {
        $config = new TwitterApiConfig();
        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = VerifyCredentialsRequestFactoryTest::createTrackingRequest();

        $psrRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('GET', $this->stringEndsWith('/2/tweets/123'))
            ->willReturn($request);

        $factory = new GetTweetRequestFactory($psrRequestFactory, $config, '123');
        $factory->create();
    }

    public function testAppendsFieldParams(): void
    {
        $config = new TwitterApiConfig();
        $fields = new TweetFieldsParams(tweetFields: ['created_at'], expansions: ['author_id']);
        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = VerifyCredentialsRequestFactoryTest::createTrackingRequest();

        $psrRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('GET', $this->logicalAnd(
                $this->stringContains('/2/tweets/456'),
                $this->stringContains('tweet.fields=created_at'),
                $this->stringContains('expansions=author_id'),
            ))
            ->willReturn($request);

        $factory = new GetTweetRequestFactory($psrRequestFactory, $config, '456', $fields);
        $factory->create();
    }

    public function testSetsAcceptHeader(): void
    {
        $config = new TwitterApiConfig();
        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = VerifyCredentialsRequestFactoryTest::createTrackingRequest();

        $psrRequestFactory->expects($this->once())
            ->method('createRequest')
            ->willReturn($request);

        $factory = new GetTweetRequestFactory($psrRequestFactory, $config, '1');
        $result = $factory->create();

        self::assertSame('application/json', $result->getHeaderLine('Accept'));
    }
}
