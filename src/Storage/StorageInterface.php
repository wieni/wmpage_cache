<?php

namespace Drupal\wmpage_cache\Storage;

use Drupal\wmpage_cache\Cache;
use Drupal\wmpage_cache\Exception\NoSuchCacheEntryException;

interface StorageInterface
{
    /**
     * @param bool $includeBody Whether or not the response body and headers
     *  should be included
     *
     * @throws NoSuchCacheEntryException
     */
    public function load(string $id, bool $includeBody = true): Cache;

    /**
     * @param string[] $ids
     *
     * @param bool $includeBody Whether or not the response body and headers
     *  should be included
     *
     * @return \Iterator An Iterator that contains Cache items
     */
    public function loadMultiple(array $ids, bool $includeBody = true): \Iterator; // I really want to enforce this

    /** @param string[] $tags */
    public function set(Cache $item, array $tags): void;

    /**
     * Note: Content nor headers will be hydrated.
     *
     * @param string[] $tags
     *
     * @return string[] The cache ids
     */
    public function getByTags(array $tags): array;

    /**
     * Remove expired items from storage.
     *
     * @param string[] The cache ids
     */
    public function getExpired(int $amount): array;

    /** @param string[] The cache ids to remove */
    public function remove(array $ids): void;

    /** Remove all cache entries. */
    public function flush(): void;
}
