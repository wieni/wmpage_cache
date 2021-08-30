<?php

namespace Drupal\wmpage_cache\Http\Middleware;

use Drupal\wmpage_cache\WmPageCacheEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Cache implements HttpKernelInterface
{
    /** @var HttpKernelInterface */
    protected $next;
    /** @var EventDispatcherInterface */
    protected $dispatcher;

    public function __construct(
        HttpKernelInterface $next,
        EventDispatcherInterface $dispatcher
    ) {
        $this->next = $next;
        $this->dispatcher = $dispatcher;
    }

    public function handle(
        Request $request,
        $type = self::MASTER_REQUEST,
        $catch = true
    ): Response {
        if ($type !== static::MASTER_REQUEST) {
            return $this->next->handle($request, $type, $catch);
        }

        $event = new GetResponseEvent($this, $request, $type);

        $this->dispatcher->dispatch(
            WmPageCacheEvents::CACHE_HANDLE,
            $event
        );

        if ($event->hasResponse()) {
            return $event->getResponse();
        }

        return $this->next->handle($request, $type, $catch);
    }
}
