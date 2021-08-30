<?php

namespace Drupal\wmpage_cache\EventSubscriber;

use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\wmpage_cache\EnrichRequest;
use Drupal\wmpage_cache\Exception\NoSuchCacheEntryException;
use Drupal\wmpage_cache\Http\CachedResponse;
use Drupal\wmpage_cache\Manager;
use Drupal\wmpage_cache\MaxAgeInterface;
use Drupal\wmpage_cache\Validation\Validation;
use Drupal\wmpage_cache\WmPageCacheEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CacheSubscriber implements EventSubscriberInterface
{
    public const CACHE_HEADER = 'X-Wm-Cache';

    /** @var RendererInterface */
    protected $renderer;
    /** @var Manager */
    protected $manager;
    /** @var Validation */
    protected $validation;
    /** @var EnrichRequest */
    protected $enrichRequest;
    /** @var MaxAgeInterface */
    protected $maxAgeStrategy;

    /** @var bool */
    protected $addHeader;
    /** @var array */
    protected $strippedHeaders = [];

    public function __construct(
        RendererInterface $renderer,
        Manager $manager,
        Validation $validation,
        EnrichRequest $enrichRequest,
        MaxAgeInterface $maxAgeStrategy,
        bool $addHeader,
        array $strippedHeaders
    ) {
        $this->renderer = $renderer;
        $this->manager = $manager;
        $this->validation = $validation;
        $this->maxAgeStrategy = $maxAgeStrategy;
        $this->addHeader = $addHeader;
        $this->enrichRequest = $enrichRequest;
        $this->strippedHeaders = $strippedHeaders;
    }

    public static function getSubscribedEvents(): array
    {
        $events[WmPageCacheEvents::CACHE_HANDLE][] = ['onEnrichRequest', 10001];
        $events[WmPageCacheEvents::CACHE_HANDLE][] = ['onGetCachedResponse', 10000];
        $events[KernelEvents::RESPONSE][] = ['onResponse', -255];
        $events[KernelEvents::TERMINATE][] = ['onTerminate', 0];

        return $events;
    }

    public function onEnrichRequest(GetResponseEvent $event): void
    {
        // Do a faster-than-drupal user and session lookup
        // Fills the Request attribute with:
        // - '_wmpage_cache.uid'
        // - '_wmpage_cache.roles'
        // - '_wmpage_cache.session'
        $this->enrichRequest->enrichRequest($event->getRequest());
    }

    public function onGetCachedResponse(GetResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $check = $this->validation->shouldIgnoreRequest($request);
        if (!$check->allowCachedResponse()) {
            return;
        }

        try {
            $response = $this->manager->get($request)->toResponse();
            // Check if we should respond with a 304
            // Not relevant atm with cache-control: max-age
            $response->isNotModified($request);

            if ($this->addHeader) {
                $response->headers->set(self::CACHE_HEADER, 'HIT');
            }

            if (empty($response->getContent())) {
                return;
            }

            $event->setResponse($response);
        } catch (NoSuchCacheEntryException $e) {
        }
    }

    public function onResponse(FilterResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if (
            $response instanceof CachedResponse
            || !$event->isMasterRequest()
            || empty($response->getContent())
        ) {
            return;
        }

        foreach ($this->strippedHeaders as $remove) {
            $response->headers->remove($remove);
        }

        if ($this->addHeader) {
            $response->headers->set(self::CACHE_HEADER, 'MISS');
        }

        // Don't override explicitly set maxage headers.
        if (
            $response->headers->hasCacheControlDirective('max-age')
            || $response->headers->hasCacheControlDirective('s-maxage')
        ) {
            return;
        }

        $check = $this->validation->shouldIgnoreResponse($request, $response);
        if (!$check->allowCached()) {
            $this->setMaxAge(
                $response,
                [
                    'maxage' => 0,
                    's-maxage' => 0,
                    'wm-s-maxage' => null,
                ]
            );
            return;
        }

        $this->setMaxAge(
            $response,
            $this->maxAgeStrategy->getMaxage($request, $response)
        );
    }

    public function onTerminate(PostResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if (
            !$response instanceof CacheableResponseInterface
            || !$event->isMasterRequest()
            || !$response->isCacheable()
        ) {
            return;
        }

        $tags = $response->getCacheableMetadata()->getCacheTags();
        $this->manager->store($request, $response, $tags);
    }

    protected function setMaxAge(Response $response, array $definition): void
    {
        if (
            !isset($definition['maxage'])
            && !isset($definition['s-maxage'])
        ) {
            return;
        }

        // Reset cache-control
        // (probably contains a must-revalidate or no-cache header)
        $response->headers->set('Cache-Control', '');

        if (empty($definition['maxage']) && empty($definition['s-maxage'])) {
            return;
        }

        // This triggers a bug in the default PageCache middleware
        // and is not actually needed according to the http spec.
        // But since clients ought to ignore it if a maxage is set,
        // it's pretty useless.
        //
        // Can be fixed from WmpageCacheServiceProvider using
        // $container->removeDefinition('http_middleware.page_cache');
        $response->headers->remove('expires');

        if (!empty($definition['maxage'])) {
            $response->setMaxAge($definition['maxage']);
        }

        if (isset($definition['s-maxage'])) {
            $response->setSharedMaxAge($definition['s-maxage']);
        }

        if (isset($definition['wm-s-maxage'])) {
            $response->headers->addCacheControlDirective(
                'wm-s-maxage',
                $definition['wm-s-maxage']
            );
        }
    }
}
