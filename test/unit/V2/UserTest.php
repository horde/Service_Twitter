<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit;

use Horde\Service\Twitter\V2\TweetEntities;
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
            'name' => 'Horde Project',
            'username' => 'hordeproject',
            'profile_image_url' => 'https://example.com/avatar.jpg',
            'description' => 'Open source groupware',
            'created_at' => '2008-01-01T00:00:00.000Z',
            'verified' => true,
            'public_metrics' => (object) [
                'followers_count' => 1000,
                'following_count' => 50,
                'tweet_count' => 200,
                'listed_count' => 25,
            ],
            'location' => 'Internet',
            'url' => 'https://www.horde.org/',
            'pinned_tweet_id' => '999',
            'protected' => false,
        ];

        $user = User::fromApiResponse($data);

        self::assertSame('12345', $user->id);
        self::assertSame('Horde Project', $user->name);
        self::assertSame('hordeproject', $user->username);
        self::assertSame('https://example.com/avatar.jpg', $user->profileImageUrl);
        self::assertSame('Open source groupware', $user->description);
        self::assertTrue($user->verified);
        self::assertSame(1000, $user->publicMetrics['followers_count']);
        self::assertSame('Internet', $user->location);
        self::assertSame('https://www.horde.org/', $user->url);
        self::assertSame('999', $user->pinnedTweetId);
        self::assertFalse($user->protected);
    }

    public function testFromApiResponseWithMinimalData(): void
    {
        $data = (object) ['id' => '1', 'name' => 'Minimal', 'username' => 'min'];

        $user = User::fromApiResponse($data);

        self::assertSame('1', $user->id);
        self::assertSame('Minimal', $user->name);
        self::assertSame('min', $user->username);
        self::assertNull($user->profileImageUrl);
        self::assertNull($user->description);
        self::assertNull($user->createdAt);
        self::assertNull($user->verified);
        self::assertNull($user->publicMetrics);
        self::assertNull($user->location);
        self::assertNull($user->url);
        self::assertNull($user->pinnedTweetId);
        self::assertNull($user->protected);
        self::assertNull($user->withheld);
        self::assertNull($user->entities);
    }

    public function testToStringReturnsAtUsername(): void
    {
        $user = User::fromApiResponse((object) ['id' => '1', 'name' => 'N', 'username' => 'someone']);

        self::assertSame('@someone', (string) $user);
    }

    public function testPublicMetricsIgnoredWhenNotObject(): void
    {
        $data = (object) [
            'id' => '1',
            'name' => 'N',
            'username' => 'n',
            'public_metrics' => 'oops',
        ];

        self::assertNull(User::fromApiResponse($data)->publicMetrics);
    }

    public function testWithheldParsedFromObject(): void
    {
        $data = (object) [
            'id' => '1',
            'name' => 'N',
            'username' => 'n',
            'withheld' => (object) [
                'country_codes' => ['DE'],
                'scope' => 'user',
            ],
        ];

        $user = User::fromApiResponse($data);

        self::assertNotNull($user->withheld);
        self::assertSame(['DE'], $user->withheld['country_codes']);
        self::assertSame('user', $user->withheld['scope']);
    }

    public function testEntitiesUrlAndDescriptionParsedAsTweetEntities(): void
    {
        $data = (object) [
            'id' => '1',
            'name' => 'N',
            'username' => 'n',
            'entities' => (object) [
                'url' => (object) [
                    'urls' => [
                        (object) ['start' => 0, 'end' => 23, 'url' => 'https://t.co/x'],
                    ],
                ],
                'description' => (object) [
                    'hashtags' => [
                        (object) ['start' => 0, 'end' => 6, 'tag' => 'horde'],
                    ],
                ],
            ],
        ];

        $user = User::fromApiResponse($data);

        self::assertNotNull($user->entities);
        self::assertArrayHasKey('url', $user->entities);
        self::assertArrayHasKey('description', $user->entities);
        self::assertInstanceOf(TweetEntities::class, $user->entities['url']);
        self::assertInstanceOf(TweetEntities::class, $user->entities['description']);
        self::assertSame('horde', $user->entities['description']->hashtags[0]['tag']);
    }

    public function testEntitiesEmptyObjectStaysNull(): void
    {
        $data = (object) [
            'id' => '1',
            'name' => 'N',
            'username' => 'n',
            'entities' => (object) [],
        ];

        self::assertNull(User::fromApiResponse($data)->entities);
    }
}
