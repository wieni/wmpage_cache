<?php

namespace Drupal\wmpage_cache;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\wmcontroller\Event\MainEntityEvent;
use Drupal\wmcontroller\WmcontrollerEvents;
use Drupal\wmpage_cache\Event\CacheInsertEvent;
use Drupal\wmpage_cache\Event\ValidationEvent;
use Drupal\wmpage_cache\Validation\CacheableRequestResult;
use Drupal\wmpage_cache\Validation\CacheableResponseResult;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Dispatcher implements DispatcherInterface
{
    /** @var EventDispatcherInterface */
    protected $dispatcher;
    /** @var CacheableMetadata */
    protected $metadata;

    public function __construct(
        EventDispatcherInterface $dispatcher
    ) {
        $this->dispatcher = $dispatcher;
        $this->metadata = new CacheableMetadata();
    }

    public function dispatchMainEntity(EntityInterface $entity)
    {
        if (!class_exists(MainEntityEvent::class) || !class_exists(WmcontrollerEvents::class)) {
            return null;
        }

        $event = new MainEntityEvent($entity);
        $this->dispatcher->dispatch(
            WmcontrollerEvents::MAIN_ENTITY_RENDER,
            $event
        );

        return $event;
    }

    public function dispatchPresented(EntityInterface $entity): void
    {
        $this->metadata->addCacheableDependency($entity);
    }

    public function dispatchTags(array $tags): void
    {
        $this->metadata->addCacheTags($tags);
    }

    public function dispatchCacheInsertEvent(Cache $cache, Request $request, Response $response, array $tags): CacheInsertEvent
    {
        $event = new CacheInsertEvent($cache, $tags, $request, $response);
        $this->dispatcher->dispatch(
            WmPageCacheEvents::CACHE_INSERT,
            $event
        );

        return $event;
    }

    public function dispatchRequestCacheablityValidation(Request $request): ValidationEvent
    {
        $event = new ValidationEvent($request, null, CacheableRequestResult::class);
        $this->dispatcher->dispatch(
            WmPageCacheEvents::VALIDATE_CACHEABILITY_REQUEST,
            $event
        );

        return $event;
    }

    public function dispatchResponseCacheablityValidation(Request $request, Response $response): ValidationEvent
    {
        $event = new ValidationEvent($request, $response, CacheableResponseResult::class);
        $this->dispatcher->dispatch(
            WmPageCacheEvents::VALIDATE_CACHEABILITY_RESPONSE,
            $event
        );

        return $event;
    }

    public function getCacheableMetadata(): CacheableMetadata
    {
        return $this->metadata;
    }
}
