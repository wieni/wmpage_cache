<?php

namespace Drupal\wmpage_cache;

use Drupal\Core\Queue\QueueFactory;
use Drupal\wmpage_cache\Plugin\QueueWorker\CacheHeaterQueueWorker;

class CacheHeater implements CacheHeaterInterface
{
    /** @var QueueFactory */
    protected $queueFactory;

    public function __construct(
        QueueFactory $queueFactory
    ) {
        $this->queueFactory = $queueFactory;
    }

    public function warmup(array $entries): void
    {
        $queue = $this->queueFactory->get(CacheHeaterQueueWorker::ID);

        foreach ($entries as $entry) {
            $queue->createItem($entry->getUri());
        }
    }

    public function warmupAll(): void
    {
    }
}
