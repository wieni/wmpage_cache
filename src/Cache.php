<?php

namespace Drupal\wmpage_cache;

use Drupal\wmpage_cache\Http\CachedResponse;

class Cache
{
    /** @var string */
    protected $id;
    /** @var string */
    protected $uri;
    /** @var string */
    protected $method;
    /** @var string */
    protected $body;
    /** @var array<string, string> */
    protected $headers;
    /** @var int */
    protected $expiry;
    /** @var int */
    protected $checksum;
    /** @var CachedResponse */
    protected $response;

    public function __construct(string $id, string $uri, string $method, string $body, array $headers, int $expiry, int $checksum)
    {
        $this->id = $id;
        $this->uri = $uri;
        $this->method = strtoupper($method);
        $this->body = $body;
        $this->headers = $headers;
        $this->expiry = $expiry;
        $this->checksum = $checksum;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    /** @return array<string, string> */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getExpiry(): int
    {
        return $this->expiry;
    }

    public function getChecksum(): int
    {
        return $this->checksum;
    }

    public function toResponse(): CachedResponse
    {
        if (isset($this->response)) {
            return $this->response;
        }

        $this->response = new CachedResponse(
            $this->body,
            CachedResponse::HTTP_OK,
            $this->headers
        );

        return $this->response;
    }
}
