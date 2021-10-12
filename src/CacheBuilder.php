<?php

namespace Drupal\wmpage_cache;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CacheBuilder implements CacheBuilderInterface, CacheSerializerInterface
{
    /** @var bool */
    protected $storeCache;
    /** @var array */
    protected $ignoredHeaders;

    public function __construct(bool $storeCache = true)
    {
        $this->storeCache = $storeCache;
    }

    public function buildCacheEntity(
        string $id,
        Request $request,
        Response $response,
        array $tags = []
    ): Cache {
        $body = '';
        $headers = [];
        if ($this->storeCache) {
            $body = $response->getContent();
            $headers = $response->headers->all();
        }

        $ttl = $this->getMaxAge($response);

        return new Cache(
            $id,
            $request->getRequestUri(),
            $request->getMethod(),
            $body,
            $headers,
            time() + $ttl
        );
    }

    public function normalize(Cache $item, bool $includeContent = true): array
    {
        return [
            'id' => $item->getId(),
            'uri' => $item->getUri(),
            'method' => $item->getMethod(),
            // base64 encoding the compressed string so the output doesn't
            // garble potential other encoders ( json_encode ).
            // The penalty is pretty bad tho.
            'content' => $includeContent
                ? base64_encode(gzcompress($item->getBody()))
                : '',
            'headers' => $includeContent
                ? serialize($item->getHeaders())
                : [],
            'expiry' => $item->getExpiry(),
        ];
    }

    public function denormalize(array $row): Cache
    {
        return new Cache(
            (string) $row['id'],
            (string) $row['uri'],
            (string) $row['method'],
            empty($row['content']) ? '' : gzuncompress(base64_decode($row['content'])),
            empty($row['headers']) ? [] : unserialize($row['headers'], ['allowed_classes' => false]),
            (int) $row['expiry']
        );
    }

    protected function getMaxAge(Response $response): ?int
    {
        /**
         * The Cache-Control header allows for extensions.
         *
         * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.9.6
         * Unrecognized cache-directives MUST be ignored; it is assumed that
         * any cache-directive likely to be unrecognized by an HTTP/1.1 cache
         * will be combined with standard directives (or the response's default
         * cacheability) such that the cache behavior will remain minimally
         * correct even if the cache does not understand the extension(s).
         */
        if ($response->headers->hasCacheControlDirective('wm-s-maxage')) {
            return (int) $response->headers->getCacheControlDirective('wm-s-maxage');
        }

        return $response->getMaxAge();
    }
}
