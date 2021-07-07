<?php

namespace Drupal\wmpage_cache;

use Drupal\wmpage_cache\Storage\StorageInterface;

class Invalidator implements InvalidatorInterface
{
    /** @var StorageInterface */
    protected $storage;
    /** @var CacheHeaterInterface */
    protected $heater;

    public function __construct(
        StorageInterface $storage,
        CacheHeaterInterface $heater
    ) {
        $this->storage = $storage;
        $this->heater = $heater;
    }

    public function invalidateCacheTags(array $tags)
    {
        $entries = $this->storage->getByTags($tags);
        $ids = array_map(
            function (Cache $entry) { return $entry->getId(); },
            $entries
        );

        $this->storage->remove($ids);
        $this->heater->warmup($entries);
    }
}
