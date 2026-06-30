<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit;

use Horde\Service\Twitter\V2\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(User::class)]
final class UserTest extends TestCase
{
    public function testFromApiResponseWithCompleteData(): void
    {
        $data = (object) [
            'id' => '12345',
            'name' => 'Alice Smith',
            'username' => 'alice',
            'profile_image_url' => 'https://pbs.twimg.com/profile/alice.jpg',
            'description' => 'Developer',
            'created_at' => '2020-01-01T00:00:00.000Z',
            'verified' => true,
            'public_metrics' => (object) [
                'followers_count' => 100,
                'following_count' => 50,
                'tweet_count' => 500,
                'listed_count' => 10,
            ],
        ];

        $user = User::fromApiResponse($data);

        self::assertSame('12345', $user->id);
        self::assertSame('Alice Smith', $user->name);
        self::assertSame('alice', $user->username);
        self::assertSame('https://pbs.twimg.com/profile/alice.jpg', $user->profileImageUrl);
        self::assertSame('Developer', $user->description);
        self::assertSame('2020-01-01T00:00:00.000Z', $user->createdAt);
        self::assertTrue($user->verified);
        self::assertSame(100, $user->publicMetrics['followers_count']);
    }

    public function testFromApiResponseWithMinimalData(): void
    {
        $data = (object) [
            'id' => '1',
            'name' => 'Bob',
            'username' => 'bob',
        ];

        $user = User::fromApiResponse($data);

        self::assertSame('1', $user->id);
        self::assertSame('Bob', $user->name);
        self::assertSame('bob', $user->username);
        self::assertNull($user->profileImageUrl);
        self::assertNull($user->description);
        self::assertNull($user->createdAt);
        self::assertNull($user->verified);
        self::assertNull($user->publicMetrics);
    }

    public function testToStringReturnsAtUsername(): void
    {
        $user = User::fromApiResponse((object) ['id' => '1', 'name' => 'Bob', 'username' => 'bob']);

        self::assertSame('@bob', (string) $user);
    }

    public function testPublicMetricsIgnoredWhenNotObject(): void
    {
        $data = (object) ['id' => '1', 'name' => 'X', 'username' => 'x', 'public_metrics' => 'not-an-object'];

        self::assertNull(User::fromApiResponse($data)->publicMetrics);
    }
}
