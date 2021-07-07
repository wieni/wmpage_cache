<?php

namespace Drupal\wmpage_cache;

interface CacheHeaterInterface
{
    /** @var Cache[] $entries */
    public function warmup(array $entries): void;

    public function warmupAll(): void;
}
