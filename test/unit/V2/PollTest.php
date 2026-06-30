<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit;

use Horde\Service\Twitter\V2\Poll;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Poll::class)]
final class PollTest extends TestCase
{
    public function testOpenPoll(): void
    {
        $data = (object) [
            'id' => '1365059861688410112',
            'options' => [
                (object) ['position' => 1, 'label' => 'Yes', 'votes' => 42],
                (object) ['position' => 2, 'label' => 'No', 'votes' => 7],
            ],
            'voting_status' => 'open',
            'end_datetime' => '2026-04-25T18:00:00.000Z',
            'duration_minutes' => 1440,
        ];

        $poll = Poll::fromApiResponse($data);

        self::assertSame('1365059861688410112', $poll->id);
        self::assertCount(2, $poll->options);
        self::assertSame('Yes', $poll->options[0]['label']);
        self::assertSame(42, $poll->options[0]['votes']);
        self::assertSame('open', $poll->votingStatus);
        self::assertSame(1440, $poll->durationMinutes);
    }

    public function testMissingOptionsYieldsEmptyArray(): void
    {
        $poll = Poll::fromApiResponse((object) ['id' => '1']);

        self::assertSame('1', $poll->id);
        self::assertSame([], $poll->options);
        self::assertNull($poll->votingStatus);
        self::assertNull($poll->endDatetime);
        self::assertNull($poll->durationMinutes);
    }

    public function testNonObjectOptionsAreSkipped(): void
    {
        $data = (object) [
            'id' => '2',
            'options' => [
                'not-an-object',
                (object) ['position' => 1, 'label' => 'Maybe', 'votes' => 3],
            ],
        ];

        $poll = Poll::fromApiResponse($data);

        self::assertCount(1, $poll->options);
        self::assertSame('Maybe', $poll->options[0]['label']);
    }
}
