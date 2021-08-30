<?php

namespace Drupal\wmpage_cache;

use Drupal\wmpage_cache\Storage\StorageInterface;

class Invalidator implements InvalidatorInterface
{
    /** @var \Drupal\wmpage_cache\Storage\StorageInterface */
    protected $storage;

    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    public function invalidateCacheTags(array $tags)
    {
        $this->storage->remove(
            $this->storage->getByTags($tags)
        );
    }
}
