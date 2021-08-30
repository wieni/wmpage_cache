<?php

namespace Drupal\wmpage_cache;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface CacheBuilderInterface
{
    /** @param string[] $tags */
    public function buildCacheEntity(string $id, Request $request, Response $response, array $tags = []): Cache;
}
