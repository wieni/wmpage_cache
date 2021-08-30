<?php

namespace Drupal\wmpage_cache;

interface CacheSerializerInterface
{
    /**
     * @param \Drupal\wmpage_cache\Cache $cache
     * @param bool $includeContent
     *
     * @return mixed
     */
    public function normalize(Cache $cache, $includeContent = true);

    /**
     * @param mixed $row
     *
     * @return \Drupal\wmpage_cache\Cache
     */
    public function denormalize($row);
}
