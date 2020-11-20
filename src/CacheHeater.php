<?php

namespace Drupal\wmpage_cache;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\wmpage_cache\Plugin\QueueWorker\CacheHeaterQueueWorker;

class CacheHeater implements CacheHeaterInterface
{
    /** @var EntityTypeManagerInterface */
    protected $entityTypeManager;
    /** @var QueueFactory */
    protected $queueFactory;
    /** @var array */
    protected $includedPages;

    public function __construct(
        EntityTypeManagerInterface $entityTypeManager,
        QueueFactory $queueFactory,
        array $includedPages
    ) {
        $this->entityTypeManager = $entityTypeManager;
        $this->queueFactory = $queueFactory;
        $this->includedPages = $includedPages;
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
        $urls = [];

        foreach ($this->includedPages['entities'] ?? [] as $entityTypeId => $bundles) {
            $definition = $this->entityTypeManager->getDefinition($entityTypeId);

            if (!$definition->hasLinkTemplate('canonical')) {
                throw new \InvalidArgumentException(
                    "Entity type with id ${$entityTypeId} does not have a canonical route"
                );
            }

            $query = $this->entityTypeManager
                ->getStorage($entityTypeId)
                ->getQuery();

            if (!empty($bundles)) {
                $query->condition($definition->getKey('bundle'), $bundles, 'IN');
            }

            foreach ($query->execute() as $id) {
                $urls[] = Url::fromRoute(
                    "entity.${entityTypeId}.canonical",
                    [$entityTypeId => $id]
                );
            }
        }

        foreach ($this->includedPages['routes'] as $routeName => $routeParameters) {
            $urls[] = Url::fromRoute($routeName, $routeParameters);
        }

        $queue = $this->queueFactory->get(CacheHeaterQueueWorker::ID);
        $anonymousUser = User::load(0);

        foreach ($urls as $url) {
            if (!$url->access($anonymousUser)) {
                continue;
            }

            $queue->createItem($url->setAbsolute(false)->toString());
        }
    }
}
