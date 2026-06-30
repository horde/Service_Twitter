<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit;

use Horde\Service\Twitter\V2\TweetEntities;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TweetEntities::class)]
final class TweetEntitiesTest extends TestCase
{
    public function testParsesAllListsWhenPresent(): void
    {
        $data = (object) [
            'urls' => [
                (object) [
                    'start' => 0,
                    'end' => 23,
                    'url' => 'https://t.co/abc',
                    'expanded_url' => 'https://example.com',
                    'display_url' => 'example.com',
                ],
            ],
            'hashtags' => [
                (object) ['start' => 24, 'end' => 30, 'tag' => 'horde'],
            ],
            'mentions' => [
                (object) ['start' => 31, 'end' => 38, 'username' => 'phpfig', 'id' => '42'],
            ],
            'cashtags' => [
                (object) ['start' => 39, 'end' => 43, 'tag' => 'AAPL'],
            ],
            'annotations' => [
                (object) [
                    'start' => 44,
                    'end' => 50,
                    'probability' => 0.95,
                    'type' => 'Organization',
                    'normalized_text' => 'Apple',
                ],
            ],
        ];

        $entities = TweetEntities::fromApiResponse($data);

        self::assertNotNull($entities->urls);
        self::assertSame('https://example.com', $entities->urls[0]['expanded_url']);
        self::assertNotNull($entities->hashtags);
        self::assertSame('horde', $entities->hashtags[0]['tag']);
        self::assertNotNull($entities->mentions);
        self::assertSame('phpfig', $entities->mentions[0]['username']);
        self::assertNotNull($entities->cashtags);
        self::assertSame('AAPL', $entities->cashtags[0]['tag']);
        self::assertNotNull($entities->annotations);
        self::assertSame('Apple', $entities->annotations[0]['normalized_text']);
    }

    public function testMissingListsStayNull(): void
    {
        $entities = TweetEntities::fromApiResponse((object) []);

        self::assertNull($entities->urls);
        self::assertNull($entities->hashtags);
        self::assertNull($entities->mentions);
        self::assertNull($entities->cashtags);
        self::assertNull($entities->annotations);
    }

    public function testNonArrayValueTreatedAsAbsent(): void
    {
        $entities = TweetEntities::fromApiResponse((object) ['urls' => 'not-a-list']);

        self::assertNull($entities->urls);
    }
}
