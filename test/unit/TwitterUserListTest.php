<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V1\Test\Unit;

use Horde\Service\Twitter\V1\TwitterUser;
use Horde\Service\Twitter\V1\TwitterUserList;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TwitterUserList::class)]
final class TwitterUserListTest extends TestCase
{
    public function testCountReturnsNumberOfUsers(): void
    {
        $users = [
            TwitterUser::fromApiResponse((object) ['id' => 1, 'screen_name' => 'a']),
            TwitterUser::fromApiResponse((object) ['id' => 2, 'screen_name' => 'b']),
        ];

        $list = new TwitterUserList($users);

        self::assertCount(2, $list);
    }

    public function testIterationYieldsUsers(): void
    {
        $users = [
            TwitterUser::fromApiResponse((object) ['id' => 1, 'screen_name' => 'alice']),
            TwitterUser::fromApiResponse((object) ['id' => 2, 'screen_name' => 'bob']),
        ];

        $list = new TwitterUserList($users);
        $names = [];
        foreach ($list as $user) {
            $names[] = $user->screenName;
        }

        self::assertSame(['alice', 'bob'], $names);
    }

    public function testEmptyListCountsZero(): void
    {
        $list = new TwitterUserList();

        self::assertCount(0, $list);
    }

    public function testToArrayReturnsOriginalArray(): void
    {
        $users = [
            TwitterUser::fromApiResponse((object) ['id' => 1]),
            TwitterUser::fromApiResponse((object) ['id' => 2]),
        ];

        $list = new TwitterUserList($users);

        self::assertSame($users, $list->toArray());
    }
}
