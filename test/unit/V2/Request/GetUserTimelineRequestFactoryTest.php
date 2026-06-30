<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit\Request;

use Horde\Service\Twitter\V1\Test\Unit\Request\VerifyCredentialsRequestFactoryTest;
use Horde\Service\Twitter\V2\Request\GetUserTimelineRequestFactory;
use Horde\Service\Twitter\V2\TweetFieldsParams;
use Horde\Service\Twitter\V2\TwitterApiConfig;
use Horde\Service\Twitter\V2\UserTimelineParams;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;

#[CoversClass(GetUserTimelineRequestFactory::class)]
final class GetUserTimelineRequestFactoryTest extends TestCase
{
    public function testCreatesGetRequestWithUserIdInPath(): void
    {
        $config = new TwitterApiConfig();
        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = VerifyCredentialsRequestFactoryTest::createTrackingRequest();

        $psrRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('GET', $this->stringContains('/2/users/42/tweets'))
            ->willReturn($request);

        $factory = new GetUserTimelineRequestFactory($psrRequestFactory, $config, '42');
        $factory->create();
    }

    public function testAppendsPaginationParams(): void
    {
        $config = new TwitterApiConfig();
        $params = new UserTimelineParams(maxResults: 25, paginationToken: 'tok');
        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = VerifyCredentialsRequestFactoryTest::createTrackingRequest();

        $psrRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('GET', $this->logicalAnd(
                $this->stringContains('max_results=25'),
                $this->stringContains('pagination_token=tok'),
            ))
            ->willReturn($request);

        $factory = new GetUserTimelineRequestFactory($psrRequestFactory, $config, '42', $params);
        $factory->create();
    }

    public function testCombinesTimelineParamsAndFieldParams(): void
    {
        $config = new TwitterApiConfig();
        $params = new UserTimelineParams(maxResults: 10);
        $fields = new TweetFieldsParams(tweetFields: ['created_at']);
        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = VerifyCredentialsRequestFactoryTest::createTrackingRequest();

        $psrRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('GET', $this->logicalAnd(
                $this->stringContains('max_results=10'),
                $this->stringContains('tweet.fields=created_at'),
            ))
            ->willReturn($request);

        $factory = new GetUserTimelineRequestFactory($psrRequestFactory, $config, '42', $params, $fields);
        $factory->create();
    }
}
