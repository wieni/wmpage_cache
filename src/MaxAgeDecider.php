<?php

namespace Drupal\wmpage_cache;

use Drupal\wmpage_cache\Event\MainEntityAlterEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class MaxAgeDecider implements EventSubscriberInterface, MaxAgeInterface
{
    /** @var RequestStack */
    protected $requestStack;
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;
    /** @var array */
    protected $expiries;
    protected $explicitMaxAges;

    public function __construct(
        RequestStack $requestStack,
        EventDispatcherInterface $eventDispatcher,
        array $expiries
    ) {
        $this->requestStack = $requestStack;
        $this->eventDispatcher = $eventDispatcher;
        $this->expiries = $expiries + ['paths' => [], 'entities' => []];
    }

    public static function getSubscribedEvents()
    {
        $events[KernelEvents::RESPONSE][] = ['onResponseEarly', 255];

        return $events;
    }

    public function onResponseEarly(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $headers = $event->getResponse()->headers;
        if (
            !$headers->hasCacheControlDirective('max-age')
            && !$headers->hasCacheControlDirective('s-maxage')
            && !$headers->hasCacheControlDirective('wm-s-maxage')
        ) {
            return;
        }

        $this->explicitMaxAges = array_filter(
            [
                'maxage' => $headers->getCacheControlDirective('max-age'),
                's-maxage' => $headers->getCacheControlDirective('s-maxage'),
                'wm-s-maxage' => $headers->getCacheControlDirective('wm-s-maxage'),
            ],
            'strlen' // Keeps 0, but removes NULL
        );
    }

    public function getMaxage(Request $request, Response $response)
    {
        $explicit = $this->explicitMaxAges ?: [];

        if (isset($explicit['maxage']) || isset($explicit['s-maxage'])) {
            return $explicit;
        }

        if (
            $request->attributes->has('_smaxage')
            || $request->attributes->has('_maxage')
        ) {
            return $explicit + [
                's-maxage' => $request->attributes->get('_smaxage', 0),
                'maxage' => $request->attributes->get('_maxage', 0),
                'wm-s-maxage' => $request->attributes->get('_wmsmaxage', null),
            ];
        }

        if ($entityExpiry = $this->getMaxAgesForMainEntity()) {
            return $explicit + $entityExpiry;
        }

        $path = $request->getPathInfo();
        foreach ($this->expiries['paths'] as $re => $definition) {
            // # should be safe... I guess
            if (!preg_match('#' . $re . '#', $path)) {
                continue;
            }

            return $explicit + $definition;
        }

        return $explicit + ['s-maxage' => 0, 'maxage' => 0, 'wm-s-maxage' => null];
    }

    protected function getMaxAgesForMainEntity()
    {
        if (!$entity = $this->getMainEntity()) {
            return null;
        }

        $type = $entity->getEntityTypeId();
        if (!isset($this->expiries['entities'][$type])) {
            return null;
        }

        $bundleDefs = $this->expiries['entities'][$type];

        $bundle = $entity->bundle();

        if (isset($bundleDefs['_default'])) {
            $bundleDefs += [$bundle => $bundleDefs['_default']];
        }

        return $bundleDefs[$bundle];
    }

    protected function getMainEntity()
    {
        if (!$request = $this->requestStack->getCurrentRequest()) {
            return null;
        }

        $entity = null;
        $routeName = $request->attributes->get('_route');

        preg_match('/entity\.(?<entityTypeId>.+)\.canonical/', $routeName, $matches);
        if (isset($matches['entityTypeId'])) {
            $entity = $request->attributes->get($matches['entityTypeId']);
        }

        $event = new MainEntityAlterEvent($entity);
        $this->eventDispatcher->dispatch(
            WmPageCacheEvents::MAIN_ENTITY_ALTER,
            $event
        );
        $entity = $event->getEntity();

        return $entity;
    }
}
