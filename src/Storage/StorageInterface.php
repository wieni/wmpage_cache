<?php

namespace Drupal\wmpage_cache\Storage;

use Drupal\wmpage_cache\Cache;
use Drupal\wmpage_cache\Exception\NoSuchCacheEntryException;

interface StorageInterface
{
    /**
     * @param string $id
     *
     * @param bool $includeBody Whether or not the response body and headers
     *  should be included
     *
     * @return Cache
     * @throws NoSuchCacheEntryException
     */
    public function load($id, $includeBody = true);

    /**
     * @param string[] $ids
     *
     * @param bool $includeBody Whether or not the response body and headers
     *  should be included
     *
     * @return \Iterator An Iterator that contains Cache items
     */
    public function loadMultiple(array $ids, $includeBody = true): \Iterator; // I really want to enforce this

    /** @param string[] $tags */
    public function set(Cache $item, array $tags);

    /**
     * Note: Content nor headers will be hydrated.
     *
     * @param string[] $tags
     *
     * @return Cache[] The cache ids
     */
    public function getByTags(array $tags): array;

    /**
     * Remove expired items from storage.
     *
     * @param string[] The cache ids
     */
    public function getExpired($amount);

    /** @param string[] The cache ids to remove */
    public function remove(array $ids);

    /** Remove all cache entries. */
    public function flush();
}
