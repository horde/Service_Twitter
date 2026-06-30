<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit\Request;

use Horde\Service\Twitter\V1\Test\Unit\Request\VerifyCredentialsRequestFactoryTest;
use Horde\Service\Twitter\V2\Request\GetUserMeRequestFactory;
use Horde\Service\Twitter\V2\TweetFieldsParams;
use Horde\Service\Twitter\V2\TwitterApiConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;

#[CoversClass(GetUserMeRequestFactory::class)]
final class GetUserMeRequestFactoryTest extends TestCase
{
    public function testCreatesGetRequestToCorrectEndpoint(): void
    {
        $config = new TwitterApiConfig();
        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = VerifyCredentialsRequestFactoryTest::createTrackingRequest();

        $psrRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('GET', $this->stringEndsWith('/2/users/me'))
            ->willReturn($request);

        $factory = new GetUserMeRequestFactory($psrRequestFactory, $config);
        $factory->create();
    }

    public function testAppendsUserFields(): void
    {
        $config = new TwitterApiConfig();
        $fields = new TweetFieldsParams(userFields: ['profile_image_url', 'description']);
        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = VerifyCredentialsRequestFactoryTest::createTrackingRequest();

        $psrRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('GET', $this->stringContains('user.fields=profile_image_url'))
            ->willReturn($request);

        $factory = new GetUserMeRequestFactory($psrRequestFactory, $config, $fields);
        $factory->create();
    }
}
