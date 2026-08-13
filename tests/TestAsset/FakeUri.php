<?php

namespace Pop\Test\TestAsset;

use Psr\Http\Message\UriInterface;

class FakeUri implements UriInterface
{

    protected string $scheme   = '';
    protected string $userInfo = '';
    protected string $host     = '';
    protected ?int $port       = null;
    protected string $path     = '';
    protected string $query    = '';
    protected string $fragment = '';

    public function __construct(string $path = '/')
    {
        $this->path = $path;
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getAuthority(): string
    {
        if ($this->host === '') {
            return '';
        }
        $authority = $this->host;
        if ($this->userInfo !== '') {
            $authority = $this->userInfo . '@' . $authority;
        }
        if ($this->port !== null) {
            $authority .= ':' . $this->port;
        }
        return $authority;
    }

    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    public function withScheme(string $scheme): UriInterface
    {
        $clone         = clone $this;
        $clone->scheme = $scheme;
        return $clone;
    }

    public function withUserInfo(string $user, ?string $password = null): UriInterface
    {
        $clone           = clone $this;
        $clone->userInfo = ($password !== null) ? ($user . ':' . $password) : $user;
        return $clone;
    }

    public function withHost(string $host): UriInterface
    {
        $clone       = clone $this;
        $clone->host = $host;
        return $clone;
    }

    public function withPort(?int $port): UriInterface
    {
        $clone       = clone $this;
        $clone->port = $port;
        return $clone;
    }

    public function withPath(string $path): UriInterface
    {
        $clone       = clone $this;
        $clone->path = $path;
        return $clone;
    }

    public function withQuery(string $query): UriInterface
    {
        $clone        = clone $this;
        $clone->query = $query;
        return $clone;
    }

    public function withFragment(string $fragment): UriInterface
    {
        $clone           = clone $this;
        $clone->fragment = $fragment;
        return $clone;
    }

    public function __toString(): string
    {
        $uri = '';
        if ($this->scheme !== '') {
            $uri .= $this->scheme . ':';
        }
        $authority = $this->getAuthority();
        if ($authority !== '') {
            $uri .= '//' . $authority;
        }
        $uri .= $this->path;
        if ($this->query !== '') {
            $uri .= '?' . $this->query;
        }
        if ($this->fragment !== '') {
            $uri .= '#' . $this->fragment;
        }
        return $uri;
    }

}
