<?php

namespace SilverStripe\View\Tests\Embed;

use Psr\Http\Message\StreamInterface;

class MockUtil
{
    public static function createStreamInterface(string $body)
    {
        return new class($body) implements StreamInterface {
            private string $body;
            public function __construct(string $body)
            {
                $this->body = $body;
            }
            public function __toString(): string
            {
                return $this->body;
            }
            public function close(): void
            {
                return;
            }
            public function detach()
            {
                return;
            }
            public function getSize(): ?int
            {
                return null;
            }
            public function tell(): int
            {
                return 0;
            }
            public function eof(): bool
            {
                return false;
            }
            public function isSeekable(): bool
            {
                return false;
            }
            public function seek(int $offset, int $whence = SEEK_SET): void
            {
                return;
            }
            public function rewind(): void
            {
                return;
            }
            public function isWritable(): bool
            {
                return false;
            }
            public function write(string $string): int
            {
                return 0;
            }
            public function isReadable(): bool
            {
                return false;
            }
            public function read(int $length): string
            {
                return '';
            }
            public function getContents(): string
            {
                return '';
            }
            public function getMetadata(?string $key = null)
            {
                return;
            }
        };
    }
}
