<?php

namespace Drupal\wmpage_cache;

use Drupal\Core\Entity\EntityInterface;
use Drupal\wmcontroller\Event\MainEntityEvent;
use Drupal\wmpage_cache\Event\CacheInsertEvent;
use Drupal\wmpage_cache\Event\ValidationEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tiny convenience wrapper around the Symfony event dispatcher
 */
interface DispatcherInterface
{
    public function dispatchPresented(EntityInterface $entity): void;

    public function dispatchTags(array $tags): void;

    /**
     * @return MainEntityEvent|null
     * @deprecated since this event is part of the wmcontroller module.
     */
    public function dispatchMainEntity(EntityInterface $entity);

    public function dispatchCacheInsertEvent(Cache $cache, Request $request, Response $response, array $tags): CacheInsertEvent;

    public function dispatchRequestCacheablityValidation(Request $request): ValidationEvent;

    public function dispatchResponseCacheablityValidation(Request $request, Response $response): ValidationEvent;
}
