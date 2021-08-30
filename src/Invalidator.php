<?php

namespace Drupal\wmpage_cache;

use Drupal\wmpage_cache\Storage\StorageInterface;

class Invalidator implements InvalidatorInterface
{
    /** @var StorageInterface */
    protected $storage;

    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    public function invalidateCacheTags(array $tags): void
    {
        $this->storage->remove(
            $this->storage->getByTags($tags)
        );
    }
}
