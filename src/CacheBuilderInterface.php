<?php

namespace Drupal\wmpage_cache;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface CacheBuilderInterface
{
    /**
     * @param string $id
     * @param string[] $tags
     *
     * @return \Drupal\wmpage_cache\Cache
     */
    public function buildCacheEntity($id, Request $request, Response $response, array $tags = []);
}
