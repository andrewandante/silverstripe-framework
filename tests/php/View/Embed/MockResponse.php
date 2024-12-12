<?php

namespace SilverStripe\View\Tests\Embed;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\MessageInterface;

class MockResponse implements ResponseInterface
{
    private EmbedUnitTest $unitTest;
    private string $firstResponse;
    private string $secondResponse;

    public function __construct(EmbedUnitTest $unitTest, string $firstResponse, string $secondResponse)
    {
        $this->unitTest = $unitTest;
        $this->firstResponse = $firstResponse;
        $this->secondResponse = $secondResponse;
    }

    public function getStatusCode(): int
    {
        return 200;
    }

    public function getBody(): StreamInterface
    {
        // first request is to the video HTML to get to find the oembed link
        // second request is to the oembed endpoint to fetch JSON
        if ($this->unitTest->getFirstRequest()) {
            return MockUtil::createStreamInterface($this->firstResponse);
        } else {
            return MockUtil::createStreamInterface($this->secondResponse);
        }
    }

    public function getReasonPhrase(): string
    {
        return '';
    }

    public function getProtocolVersion(): string
    {
        return '';
    }

    public function getHeaders(): array
    {
        return [];
    }

    public function getHeader($name): array
    {
        return [];
    }

    public function getHeaderLine($name): string
    {
        if (strtolower($name) === 'content-type') {
            return 'text/html; charset=utf-8';
        }
        return '';
    }

    public function hasHeader($name): bool
    {
        return false;
    }

    public function withHeader($name, $value): MessageInterface
    {
        return $this;
    }

    public function withAddedHeader($name, $value): MessageInterface
    {
        return $this;
    }

    public function withBody(StreamInterface $body): MessageInterface
    {
        return $this;
    }

    public function withoutHeader($name): MessageInterface
    {
        return $this;
    }

    public function withProtocolVersion($version): MessageInterface
    {
        return $this;
    }

    public function withStatus($code, $reasonPhrase = ''): ResponseInterface
    {
        return $this;
    }
}
