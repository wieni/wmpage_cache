<?php

namespace Drupal\wmpage_cache;

interface InvalidatorInterface
{
    public function invalidateCacheTags(array $tags);
}
