<?php

namespace Drupal\wmpage_cache\Commands;

use Drupal\wmpage_cache\CacheHeaterInterface;
use Drush\Commands\DrushCommands;

class CacheWarmupCommands extends DrushCommands
{
    /** @var CacheHeaterInterface */
    protected $heater;

    public function __construct(
        CacheHeaterInterface $heater
    ) {
        $this->heater = $heater;
    }

    /**
     * Warm up the page cache of all configured pages
     *
     * @command wmpage_cache:warmup
     * @aliases wmpage-cache-warmup,wmpcw
     */
    public function cacheWarmup(): void
    {
        $this->heater->warmupAll();
    }
}
