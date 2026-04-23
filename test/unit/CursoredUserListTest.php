<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V1\Test\Unit;

use Horde\Service\Twitter\V1\CursoredUserList;
use Horde\Service\Twitter\V1\TwitterUser;
use Horde\Service\Twitter\V1\TwitterUserList;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CursoredUserList::class)]
#[CoversClass(TwitterUserList::class)]
#[CoversClass(TwitterUser::class)]
final class CursoredUserListTest extends TestCase
{
    public function testFromApiResponseParsesUsersAndCursors(): void
    {
        $data = (object) [
            'users' => [
                (object) ['id' => 1, 'screen_name' => 'alice'],
                (object) ['id' => 2, 'screen_name' => 'bob'],
            ],
            'next_cursor' => 123456,
            'previous_cursor' => 0,
        ];

        $list = CursoredUserList::fromApiResponse($data);

        self::assertCount(2, $list->users);
        self::assertSame(123456, $list->nextCursor);
        self::assertSame(0, $list->previousCursor);

        $users = $list->users->toArray();
        self::assertSame('alice', $users[0]->screenName);
        self::assertSame('bob', $users[1]->screenName);
    }

    public function testHasMoreReturnsTrueWhenNextCursorNonZero(): void
    {
        $list = new CursoredUserList(
            users: new TwitterUserList(),
            nextCursor: 999,
            previousCursor: 0,
        );

        self::assertTrue($list->hasMore());
    }

    public function testHasMoreReturnsFalseWhenNextCursorZero(): void
    {
        $list = new CursoredUserList(
            users: new TwitterUserList(),
            nextCursor: 0,
            previousCursor: 123,
        );

        self::assertFalse($list->hasMore());
    }
}
