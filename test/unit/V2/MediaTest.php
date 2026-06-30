<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V2\Test\Unit;

use Horde\Service\Twitter\V2\Media;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Media::class)]
final class MediaTest extends TestCase
{
    public function testPhotoMedia(): void
    {
        $data = (object) [
            'media_key' => '3_1234567890',
            'type' => 'photo',
            'url' => 'https://pbs.twimg.com/media/abc.jpg',
            'width' => 1200,
            'height' => 800,
            'alt_text' => 'A horde of developers',
        ];

        $media = Media::fromApiResponse($data);

        self::assertSame('3_1234567890', $media->mediaKey);
        self::assertSame('photo', $media->type);
        self::assertSame('https://pbs.twimg.com/media/abc.jpg', $media->url);
        self::assertSame(1200, $media->width);
        self::assertSame(800, $media->height);
        self::assertSame('A horde of developers', $media->altText);
        self::assertNull($media->durationMs);
        self::assertNull($media->publicMetrics);
    }

    public function testVideoMediaWithMetrics(): void
    {
        $data = (object) [
            'media_key' => '13_99',
            'type' => 'video',
            'preview_image_url' => 'https://pbs.twimg.com/preview.jpg',
            'duration_ms' => 30000,
            'public_metrics' => (object) [
                'view_count' => 1234,
            ],
        ];

        $media = Media::fromApiResponse($data);

        self::assertSame('video', $media->type);
        self::assertSame('https://pbs.twimg.com/preview.jpg', $media->previewImageUrl);
        self::assertSame(30000, $media->durationMs);
        self::assertNotNull($media->publicMetrics);
        self::assertSame(1234, $media->publicMetrics['view_count']);
    }

    public function testMinimalMedia(): void
    {
        $media = Media::fromApiResponse((object) ['media_key' => 'x', 'type' => 'photo']);

        self::assertSame('x', $media->mediaKey);
        self::assertSame('photo', $media->type);
        self::assertNull($media->url);
        self::assertNull($media->width);
    }
}
