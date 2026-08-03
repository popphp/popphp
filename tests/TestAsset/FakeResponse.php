<?php

namespace Pop\Test\TestAsset;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class FakeResponse implements ResponseInterface
{

    protected string $protocolVersion = '1.1';
    protected array $headers          = [];
    protected StreamInterface $body;
    protected int $statusCode;
    protected string $reasonPhrase;

    public function __construct(int $statusCode = 200, string $reasonPhrase = '')
    {
        $this->statusCode   = $statusCode;
        $this->reasonPhrase = $reasonPhrase;
        $this->body         = new FakeStream();
    }

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion(string $version): MessageInterface
    {
        $clone                  = clone $this;
        $clone->protocolVersion = $version;
        return $clone;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader(string $name): bool
    {
        foreach (array_keys($this->headers) as $key) {
            if (strcasecmp($key, $name) === 0) {
                return true;
            }
        }
        return false;
    }

    public function getHeader(string $name): array
    {
        foreach ($this->headers as $key => $values) {
            if (strcasecmp($key, $name) === 0) {
                return $values;
            }
        }
        return [];
    }

    public function getHeaderLine(string $name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    public function withHeader(string $name, $value): MessageInterface
    {
        $clone                 = clone $this;
        $clone->headers[$name] = is_array($value) ? $value : [$value];
        return $clone;
    }

    public function withAddedHeader(string $name, $value): MessageInterface
    {
        $clone                 = clone $this;
        $existing              = $clone->getHeader($name);
        $clone->headers[$name] = array_merge($existing, is_array($value) ? $value : [$value]);
        return $clone;
    }

    public function withoutHeader(string $name): MessageInterface
    {
        $clone = clone $this;
        foreach (array_keys($clone->headers) as $key) {
            if (strcasecmp($key, $name) === 0) {
                unset($clone->headers[$key]);
            }
        }
        return $clone;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body): MessageInterface
    {
        $clone       = clone $this;
        $clone->body = $body;
        return $clone;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        $clone               = clone $this;
        $clone->statusCode   = $code;
        $clone->reasonPhrase = $reasonPhrase;
        return $clone;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

}
