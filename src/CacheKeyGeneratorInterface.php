<?php

namespace Drupal\wmpage_cache;

use Symfony\Component\HttpFoundation\Request;

interface CacheKeyGeneratorInterface
{
    public function generateCacheKey(Request $request);
}
