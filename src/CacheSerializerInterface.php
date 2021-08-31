<?php

namespace Drupal\wmpage_cache;

interface CacheSerializerInterface
{
    public function normalize(Cache $cache, bool $includeContent = true): array;

    public function denormalize(array $row): Cache;
}
