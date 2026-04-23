<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V1\Test\Unit;

use Horde\Service\Twitter\V1\Tweet;
use Horde\Service\Twitter\V1\TweetList;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TweetList::class)]
final class TweetListTest extends TestCase
{
    public function testCountReturnsNumberOfTweets(): void
    {
        $tweets = [
            Tweet::fromApiResponse((object) ['id' => 1, 'text' => 'First']),
            Tweet::fromApiResponse((object) ['id' => 2, 'text' => 'Second']),
            Tweet::fromApiResponse((object) ['id' => 3, 'text' => 'Third']),
        ];

        $list = new TweetList($tweets);

        self::assertCount(3, $list);
    }

    public function testIterationYieldsTweets(): void
    {
        $tweets = [
            Tweet::fromApiResponse((object) ['id' => 1, 'text' => 'A']),
            Tweet::fromApiResponse((object) ['id' => 2, 'text' => 'B']),
        ];

        $list = new TweetList($tweets);
        $collected = [];
        foreach ($list as $tweet) {
            $collected[] = $tweet->text;
        }

        self::assertSame(['A', 'B'], $collected);
    }

    public function testEmptyListCountsZero(): void
    {
        $list = new TweetList();

        self::assertCount(0, $list);
    }

    public function testToArrayReturnsOriginalArray(): void
    {
        $tweets = [
            Tweet::fromApiResponse((object) ['id' => 1]),
            Tweet::fromApiResponse((object) ['id' => 2]),
        ];

        $list = new TweetList($tweets);

        self::assertSame($tweets, $list->toArray());
    }
}
