<?php

namespace Drupal\wmpage_cache;

final class WmPageCacheEvents
{
    /**
     * Will be triggered from the Cache http middleware when a request
     * is suited for a cached response.
     *
     * The event object is an instance of
     * @see \Symfony\Component\HttpKernel\Event\GetResponseEvent
     *
     * If a response is set on the event object no further processing will occur
     * and the response is served.
     */
    public const CACHE_HANDLE = 'cache.handle';

    /**
     * Will be triggered from the Cache manager when a response is stored.
     *
     * The event object is an instance of
     * @see \Drupal\wmpage_cache\Event\CacheInsertEvent
     */
    public const CACHE_INSERT = 'cache.insert';

    /**
     * Will be triggered from the Cache http middleware when a request
     * should be validated.
     *
     * The event object is an instance of
     * @see \Drupal\wmpage_cache\Event\ValidationEvent
     */
    public const VALIDATE_CACHEABILITY_REQUEST = 'cache.request.validate';

    /**
     * Will be triggered from the CacheSubscriber when a response
     * should be validated
     *
     * The event object is an instance of
     * @see \Drupal\wmpage_cache\Event\ValidationEvent
     */
    public const VALIDATE_CACHEABILITY_RESPONSE = 'cache.response.validate';

    /**
     * Alter the entity which is used to determine the default cache
     * control directives.
     *
     * The event object is an instance of
     * @see \Drupal\wmpage_cache\Event\MainEntityAlterEvent
     */
    public const MAIN_ENTITY_ALTER = 'wmpage_cache.maxage_alter';
}
