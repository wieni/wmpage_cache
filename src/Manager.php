<?php

namespace Drupal\wmpage_cache;

use Drupal\Core\Cache\CacheTagsChecksumInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\wmpage_cache\Event\CacheInsertEvent;
use Drupal\wmpage_cache\Exception\NoSuchCacheEntryException;
use Drupal\wmpage_cache\Storage\StorageInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Manager implements CacheTagsInvalidatorInterface
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;
    /** @var StorageInterface */
    protected $storage;
    /** @var InvalidatorInterface */
    protected $invalidator;
    /** @var CacheKeyGeneratorInterface */
    protected $cacheKeyGenerator;
    /** @var CacheBuilderInterface */
    protected $cacheBuilder;
    /** @var bool */
    protected $storeCache;
    /** @var bool */
    protected $storeTags;
    /** @var int */
    protected $maxPurgesPerInvalidation;
    /** @var string[] */
    protected $ignoredCacheTags;
    /** @var string[] */
    protected $flushTriggerTags;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        StorageInterface $storage,
        InvalidatorInterface $invalidator,
        CacheKeyGeneratorInterface $cacheKeyGenerator,
        CacheBuilderInterface $cacheBuilder,
        bool $storeCache,
        bool $storeTags,
        int $maxPurgesPerInvalidation,
        array $ignoredCacheTags,
        array $flushTriggerTags
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->storage = $storage;
        $this->invalidator = $invalidator;
        $this->cacheKeyGenerator = $cacheKeyGenerator;
        $this->cacheBuilder = $cacheBuilder;
        $this->storeCache = $storeCache && $storeTags;
        $this->storeTags = $storeTags;
        $this->maxPurgesPerInvalidation = $maxPurgesPerInvalidation;
        $this->ignoredCacheTags = array_filter($ignoredCacheTags);
        $this->flushTriggerTags = array_filter($flushTriggerTags);
    }

    public function get(Request $request): Cache
    {
        if (!$this->storeCache) {
            throw new NoSuchCacheEntryException('cache_disabled');
        }

        return $this->storage->load(
            $this->cacheKeyGenerator->generateCacheKey($request)
        );
    }

    public function store(Request $request, Response $response, array $tags): void
    {
        if (!$this->storeTags) {
            return;
        }

        $cache = $this->cacheBuilder->buildCacheEntity(
            $this->cacheKeyGenerator->generateCacheKey($request),
            $request,
            $response,
            $tags
        );

        // Avoid useless writes.
        if ($cache->getChecksum() === CacheTagsChecksumInterface::INVALID_CHECKSUM_WHILE_IN_TRANSACTION) {
            return;
        }

        $event = new CacheInsertEvent($cache, $tags, $request, $response);
        $this->eventDispatcher->dispatch(
            WmPageCacheEvents::CACHE_INSERT,
            $event
        );

        if ($event->getCache()) {
            $this->storage->set($event->getCache(), $event->getTags());
        }
    }

    public function invalidateTags(array $tags): void
    {
        $filter = function ($tag): bool {
            foreach ($this->ignoredCacheTags as $re) {
                if (preg_match('#' . $re . '#', $tag)) {
                    return false;
                }
            }
            return true;
        };

        // Remove ignored tags
        $tags = array_filter($tags, $filter);

        // Check if any tag matches a flushTriggerTags regex
        // If so, flush the entire cache instead.
        foreach ($tags as $tag) {
            foreach ($this->flushTriggerTags as $re) {
                if (preg_match('#' . $re . '#', $tag)) {
                    $this->storage->flush();
                    return;
                }
            }
        }

        $this->invalidator->invalidateCacheTags($tags);
    }
}
