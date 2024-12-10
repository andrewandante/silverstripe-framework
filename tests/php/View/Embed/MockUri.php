<?php

namespace SilverStripe\View\Tests\Embed;

use Psr\Http\Message\UriInterface;
use Stringable;

class MockUri implements UriInterface, Stringable
{
    private string $scheme = '';
    private string $host = '';
    private string $path = '';
    private string $query = '';

    public function __construct(string $url)
    {
        $p = parse_url($url ?? '');
        $this->scheme = $p['scheme'] ?? '';
        $this->host = $p['host'] ?? '';
        $this->path = $p['path'] ?? '';
        $this->query = $p['query'] ?? '';
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getPort(): ?int
    {
        return null;
    }

    public function getAuthority(): string
    {
        return '';
    }

    public function getUserInfo(): string
    {
        return '';
    }

    public function getFragment(): string
    {
        return '';
    }

    public function withPath($path): UriInterface
    {
        return $this;
    }

    public function withScheme($scheme): UriInterface
    {
        return $this;
    }

    public function withUserInfo($user, $password = null): UriInterface
    {
        return $this;
    }

    public function withHost($host): UriInterface
    {
        return $this;
    }

    public function withPort($port): UriInterface
    {
        return $this;
    }

    public function withQuery($query): UriInterface
    {
        return $this;
    }

    public function withFragment($fragment): UriInterface
    {
        return $this;
    }

    public function __toString(): string
    {
        $query = $this->getQuery();
        return sprintf(
            '%s://%s%s%s',
            $this->getScheme(),
            $this->getHost(),
            '/' . ltrim($this->getPath() ?? '', '/'),
            $query ? "?$query" : ''
        );
    }
}
