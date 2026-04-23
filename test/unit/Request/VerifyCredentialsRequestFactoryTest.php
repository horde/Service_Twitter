<?php

declare(strict_types=1);

namespace Horde\Service\Twitter\V1\Test\Unit\Request;

use Horde\Service\Twitter\V1\Request\VerifyCredentialsRequestFactory;
use Horde\Service\Twitter\V1\TwitterApiConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

#[CoversClass(VerifyCredentialsRequestFactory::class)]
final class VerifyCredentialsRequestFactoryTest extends TestCase
{
    public function testCreatesGetRequestToCorrectUrl(): void
    {
        $config = new TwitterApiConfig();
        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = self::createTrackingRequest();

        $psrRequestFactory->expects($this->once())
            ->method('createRequest')
            ->with('GET', $this->stringEndsWith('/account/verify_credentials.json'))
            ->willReturn($request);

        $factory = new VerifyCredentialsRequestFactory($psrRequestFactory, $config);
        $factory->create();
    }

    public function testSetsAcceptHeader(): void
    {
        $config = new TwitterApiConfig();
        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = self::createTrackingRequest();

        $psrRequestFactory->expects($this->once())
            ->method('createRequest')
            ->willReturn($request);

        $factory = new VerifyCredentialsRequestFactory($psrRequestFactory, $config);
        $result = $factory->create();

        self::assertTrue($result->hasHeader('Accept'));
        self::assertSame('application/json', $result->getHeaderLine('Accept'));
    }

    public static function createTrackingRequest(): RequestInterface
    {
        return new class implements RequestInterface {
            private array $headers = [];
            private ?StreamInterface $body = null;

            public function withHeader(string $name, $value): static
            {
                $clone = clone $this;
                $clone->headers[$name] = is_array($value) ? $value : [$value];
                return $clone;
            }

            public function hasHeader(string $name): bool
            {
                return isset($this->headers[$name]);
            }

            public function getHeaderLine(string $name): string
            {
                return isset($this->headers[$name]) ? implode(', ', $this->headers[$name]) : '';
            }

            public function getHeader(string $name): array
            {
                return $this->headers[$name] ?? [];
            }

            public function getHeaders(): array
            {
                return $this->headers;
            }
            public function getProtocolVersion(): string
            {
                return '1.1';
            }
            public function withProtocolVersion(string $version): static
            {
                return $this;
            }
            public function withAddedHeader(string $name, $value): static
            {
                return $this;
            }
            public function withoutHeader(string $name): static
            {
                return $this;
            }
            public function getBody(): StreamInterface
            {
                return $this->body ?? new class implements StreamInterface {
                    public function __toString(): string
                    {
                        return '';
                    } public function close(): void {} public function detach()
                    {
                        return null;
                    } public function getSize(): ?int
                    {
                        return 0;
                    } public function tell(): int
                    {
                        return 0;
                    } public function eof(): bool
                    {
                        return true;
                    } public function isSeekable(): bool
                    {
                        return false;
                    } public function seek(int $offset, int $whence = SEEK_SET): void {} public function rewind(): void {} public function isWritable(): bool
                    {
                        return false;
                    } public function write(string $string): int
                    {
                        return 0;
                    } public function isReadable(): bool
                    {
                        return false;
                    } public function read(int $length): string
                    {
                        return '';
                    } public function getContents(): string
                    {
                        return '';
                    } public function getMetadata(?string $key = null)
                    {
                        return null;
                    }
                };
            }
            public function withBody(StreamInterface $body): static
            {
                $c = clone $this;
                $c->body = $body;
                return $c;
            }
            public function getRequestTarget(): string
            {
                return '/';
            }
            public function withRequestTarget(string $requestTarget): static
            {
                return $this;
            }
            public function getMethod(): string
            {
                return 'GET';
            }
            public function withMethod(string $method): static
            {
                return $this;
            }
            public function getUri(): UriInterface
            {
                return new class implements UriInterface {
                    public function getScheme(): string
                    {
                        return '';
                    } public function getAuthority(): string
                    {
                        return '';
                    } public function getUserInfo(): string
                    {
                        return '';
                    } public function getHost(): string
                    {
                        return '';
                    } public function getPort(): ?int
                    {
                        return null;
                    } public function getPath(): string
                    {
                        return '';
                    } public function getQuery(): string
                    {
                        return '';
                    } public function getFragment(): string
                    {
                        return '';
                    } public function withScheme(string $scheme): static
                    {
                        return $this;
                    } public function withUserInfo(string $user, ?string $password = null): static
                    {
                        return $this;
                    } public function withHost(string $host): static
                    {
                        return $this;
                    } public function withPort(?int $port): static
                    {
                        return $this;
                    } public function withPath(string $path): static
                    {
                        return $this;
                    } public function withQuery(string $query): static
                    {
                        return $this;
                    } public function withFragment(string $fragment): static
                    {
                        return $this;
                    } public function __toString(): string
                    {
                        return '';
                    }
                };
            }
            public function withUri(UriInterface $uri, bool $preserveHost = false): static
            {
                return $this;
            }
        };
    }
}
