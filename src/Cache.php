<?php

namespace Drupal\wmpage_cache;

use Drupal\wmpage_cache\Http\CachedResponse;

class Cache
{
    protected $id;
    protected $uri;
    protected $method;
    protected $body;
    protected $headers;
    protected $expiry;

    /** @var CachedResponse */
    protected $response;

    public function __construct($id, $uri, $method, $body, array $headers, $expiry)
    {
        $this->id = $id;
        $this->uri = $uri;
        $this->method = strtoupper($method);
        $this->body = $body;
        $this->headers = $headers;
        $this->expiry = $expiry;
    }

    /** @return string */
    public function getId()
    {
        return $this->id;
    }

    /** @return string */
    public function getUri()
    {
        return $this->uri;
    }

    /** @return string */
    public function getMethod()
    {
        return $this->method;
    }

    /** @return string */
    public function getBody()
    {
        return $this->body;
    }

    /** @return array */
    public function getHeaders()
    {
        return $this->headers;
    }

    /** @return int */
    public function getExpiry()
    {
        return $this->expiry;
    }

    /** @return CachedResponse */
    public function toResponse()
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
