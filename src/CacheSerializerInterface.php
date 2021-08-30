<?php

namespace Drupal\wmpage_cache;

interface CacheSerializerInterface
{
    /** @return mixed */
    public function normalize(Cache $cache, bool $includeContent = true);

    /** @param $row mixed */
    public function denormalize($row): Cache;
}
