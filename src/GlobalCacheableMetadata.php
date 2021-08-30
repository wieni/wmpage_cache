<?php

namespace Drupal\wmpage_cache;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Render\RendererInterface;

class GlobalCacheableMetadata
{
    /** @var RendererInterface */
    protected $renderer;

    public function __construct(
        RendererInterface $renderer
    ) {
        $this->renderer = $renderer;
    }

    public function addCacheableDependency(CacheableDependencyInterface $dependency)
    {
        $metadata = new CacheableMetadata();
        $metadata->addCacheableDependency($dependency);

        $this->applyCacheableMetadata($metadata);
    }

    public function addCacheContexts(array $cacheContexts)
    {
        $metadata = new CacheableMetadata();
        $metadata->addCacheContexts($cacheContexts);

        $this->applyCacheableMetadata($metadata);
    }

    public function addCacheTags(array $cacheTags)
    {
        $metadata = new CacheableMetadata();
        $metadata->addCacheTags($cacheTags);

        $this->applyCacheableMetadata($metadata);
    }

    public function applyCacheableMetadata(CacheableMetadata $metadata)
    {
        $build = [];
        $metadata->applyTo($build);
        $this->renderer->render($build);
    }
}
