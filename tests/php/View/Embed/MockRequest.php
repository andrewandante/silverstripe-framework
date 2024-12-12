<?php

namespace SilverStripe\View\Tests\Embed;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\MessageInterface;

class MockRequest implements RequestInterface
{
    private EmbedUnitTest $unitTest;
    private MockUri $mockUri;

    public function __construct(EmbedUnitTest $unitTest, MockUri $mockUri)
    {
        $this->unitTest = $unitTest;
        $this->mockUri = $mockUri;
    }

    public function getRequestTarget(): string
    {
        return '';
    }

    public function getMethod(): string
    {
        return '';
    }

    public function getUri(): UriInterface
    {
        $this->unitTest->setFirstRequest(false);
        return $this->mockUri;
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
        return '';
    }

    public function getBody(): StreamInterface
    {
        return MockUtil::createStreamInterface('');
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

    public function withoutHeader($name): MessageInterface
    {
        return $this;
    }

    public function withBody(StreamInterface $body): MessageInterface
    {
        return $this;
    }

    public function withProtocolVersion($version): MessageInterface
    {
        return $this;
    }

    public function withRequestTarget($requestTarget): RequestInterface
    {
        return $this;
    }

    public function withMethod($method): RequestInterface
    {
        return $this;
    }

    public function withUri(UriInterface $uri, $preserveHost = false): RequestInterface
    {
        return $this;
    }
}
