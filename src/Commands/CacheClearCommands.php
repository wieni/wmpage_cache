<?php

namespace Drupal\wmpage_cache\Commands;

use Drupal\wmpage_cache\Storage\StorageInterface;
use Drush\Commands\DrushCommands;

class CacheClearCommands extends DrushCommands
{
    /** @var StorageInterface */
    protected $storage;

    public function __construct(
        StorageInterface $storage
    ) {
        $this->storage = $storage;
    }

    /**
     * Adds a cache clear option.
     *
     * @hook on-event cache-clear
     */
    public function cacheClear(&$types, $include_bootstrapped_types)
    {
        if (!$include_bootstrapped_types) {
            return;
        }

        $types['wmpage_cache'] = [$this->storage, 'flush'];
    }
}
