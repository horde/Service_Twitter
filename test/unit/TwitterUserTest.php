<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V1\Test\Unit;

use Horde\Service\Twitter\V1\TwitterUser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TwitterUser::class)]
final class TwitterUserTest extends TestCase
{
    public function testFromApiResponseWithCompleteData(): void
    {
        $data = (object) [
            'id' => 42,
            'name' => 'Test User',
            'screen_name' => 'testuser',
            'description' => 'A test user',
            'profile_image_url_https' => 'https://pbs.twimg.com/profile/img.jpg',
            'profile_image_url' => 'http://pbs.twimg.com/profile/img.jpg',
            'followers_count' => 100,
            'friends_count' => 50,
            'statuses_count' => 200,
            'created_at' => 'Mon Jan 01 00:00:00 +0000 2020',
            'verified' => true,
            'protected' => true,
        ];

        $user = TwitterUser::fromApiResponse($data);

        self::assertSame(42, $user->id);
        self::assertSame('Test User', $user->name);
        self::assertSame('testuser', $user->screenName);
        self::assertSame('A test user', $user->description);
        self::assertSame('https://pbs.twimg.com/profile/img.jpg', $user->profileImageUrl);
        self::assertSame(100, $user->followersCount);
        self::assertSame(50, $user->friendsCount);
        self::assertSame(200, $user->statusesCount);
        self::assertSame('Mon Jan 01 00:00:00 +0000 2020', $user->createdAt);
        self::assertTrue($user->verified);
        self::assertTrue($user->protected);
    }

    public function testFromApiResponsePrefersHttpsProfileImage(): void
    {
        $data = (object) [
            'profile_image_url_https' => 'https://secure.example.com/img.jpg',
            'profile_image_url' => 'http://insecure.example.com/img.jpg',
        ];

        $user = TwitterUser::fromApiResponse($data);

        self::assertSame('https://secure.example.com/img.jpg', $user->profileImageUrl);
    }

    public function testFromApiResponseFallsBackToHttpProfileImage(): void
    {
        $data = (object) [
            'profile_image_url' => 'http://fallback.example.com/img.jpg',
        ];

        $user = TwitterUser::fromApiResponse($data);

        self::assertSame('http://fallback.example.com/img.jpg', $user->profileImageUrl);
    }

    public function testFromApiResponseWithMinimalData(): void
    {
        $user = TwitterUser::fromApiResponse((object) []);

        self::assertSame(0, $user->id);
        self::assertSame('', $user->name);
        self::assertSame('', $user->screenName);
        self::assertSame('', $user->description);
        self::assertSame('', $user->profileImageUrl);
        self::assertSame(0, $user->followersCount);
        self::assertSame(0, $user->friendsCount);
        self::assertSame(0, $user->statusesCount);
        self::assertSame('', $user->createdAt);
        self::assertFalse($user->verified);
        self::assertFalse($user->protected);
    }

    public function testToStringReturnsAtScreenName(): void
    {
        $data = (object) ['screen_name' => 'hikikomori'];

        $user = TwitterUser::fromApiResponse($data);

        self::assertSame('@hikikomori', (string) $user);
    }
}
