<?php

namespace Pop\Test\TestAsset;

use Psr\Http\Message\StreamInterface;

class FakeStream implements StreamInterface
{

    protected $resource;

    public function __construct(string $contents = '')
    {
        $this->resource = fopen('php://memory', 'r+');
        if ($contents !== '') {
            fwrite($this->resource, $contents);
            fseek($this->resource, 0);
        }
    }

    public function __toString(): string
    {
        if (!is_resource($this->resource)) {
            return '';
        }
        $this->rewind();
        return $this->getContents();
    }

    public function close(): void
    {
        if (is_resource($this->resource)) {
            fclose($this->resource);
        }
        $this->resource = null;
    }

    public function detach()
    {
        $resource       = $this->resource;
        $this->resource = null;
        return $resource;
    }

    public function getSize(): ?int
    {
        if (!is_resource($this->resource)) {
            return null;
        }
        $stats = fstat($this->resource);
        return $stats['size'] ?? null;
    }

    public function tell(): int
    {
        return ftell($this->resource);
    }

    public function eof(): bool
    {
        return feof($this->resource);
    }

    public function isSeekable(): bool
    {
        return true;
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        fseek($this->resource, $offset, $whence);
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function isWritable(): bool
    {
        return true;
    }

    public function write(string $string): int
    {
        return fwrite($this->resource, $string);
    }

    public function isReadable(): bool
    {
        return true;
    }

    public function read(int $length): string
    {
        return fread($this->resource, $length);
    }

    public function getContents(): string
    {
        return stream_get_contents($this->resource);
    }

    public function getMetadata(?string $key = null): mixed
    {
        $meta = stream_get_meta_data($this->resource);
        return ($key === null) ? $meta : ($meta[$key] ?? null);
    }

}
