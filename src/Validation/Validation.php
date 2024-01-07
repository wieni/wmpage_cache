<?php

namespace Drupal\wmpage_cache\Validation;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\PageCache\ResponsePolicyInterface;
use Drupal\wmpage_cache\EnrichRequest;
use Drupal\wmpage_cache\Event\ValidationEvent;
use Drupal\wmpage_cache\WmPageCacheEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Validation implements EventSubscriberInterface
{
    /** @var array<int, bool> */
    protected $cacheableStatusCodes = [
        Response::HTTP_OK => true,
        Response::HTTP_NON_AUTHORITATIVE_INFORMATION => true,
        Response::HTTP_MULTIPLE_CHOICES => true,
        Response::HTTP_MOVED_PERMANENTLY => true,
        Response::HTTP_FOUND => true,
        Response::HTTP_NOT_FOUND => true,
        Response::HTTP_GONE => true,
    ];

    /** @var array<string, bool> */
    protected $cacheableMethods = [
        Request::METHOD_GET => true,
        Request::METHOD_HEAD => true,
        Request::METHOD_OPTIONS => true,
    ];

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;
    /** @var ResponsePolicyInterface */
    protected $cacheResponsePolicy;
    /** @var bool */
    protected $ignoreAuthenticatedUsers;
    /** @var bool */
    protected $storeResponse;
    /** @var bool */
    protected $storeTags;
    /** @var array */
    protected $ignoredRoles;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ResponsePolicyInterface $cacheResponsePolicy,
        $ignoreAuthenticatedUsers,
        $storeResponse,
        $storeTags,
        array $ignoredRoles = []
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->cacheResponsePolicy = $cacheResponsePolicy;
        $this->ignoreAuthenticatedUsers = $ignoreAuthenticatedUsers;
        $this->storeResponse = $storeResponse && $storeTags;
        $this->storeTags = $storeTags;
        $this->ignoredRoles = $ignoredRoles;
    }

    public static function getSubscribedEvents(): array
    {
        $events[WmPageCacheEvents::VALIDATE_CACHEABILITY_REQUEST][] = 'onShouldIgnoreRequest';
        $events[WmPageCacheEvents::VALIDATE_CACHEABILITY_RESPONSE][] = 'onShouldIgnoreResponse';

        return $events;
    }

    public function shouldIgnoreRequest(Request $request): CacheableRequestResult
    {
        // Don't even go through the motion if we are basically disabled
        if (!$this->storeResponse) {
            return new CacheableRequestResult(AccessResult::forbidden('Not storing any cache.'));
        }

        $event = new ValidationEvent(
            $request,
            null,
            CacheableRequestResult::class
        );
        $this->eventDispatcher->dispatch(
            $event,
            WmPageCacheEvents::VALIDATE_CACHEABILITY_REQUEST
        );

        return $event->result();
    }

    public function shouldIgnoreResponse(Request $request, Response $response): CacheableResponseResult
    {
        $event = new ValidationEvent(
            $request,
            $response,
            CacheableResponseResult::class
        );

        $this->eventDispatcher->dispatch(
            $event,
            WmPageCacheEvents::VALIDATE_CACHEABILITY_RESPONSE
        );

        return $event->result();
    }

    public function onShouldIgnoreRequest(ValidationEvent $event): void
    {
        $request = $event->getRequest();

        $event->add($this->isCacheableMethod($request));
        $event->add($this->authenticationCheck($request));
        $event->add($this->roleCheck($request));
        $event->add($this->isNotAdmin($request));
    }

    public function onShouldIgnoreResponse(ValidationEvent $event): void
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        // Run checks for the request
        // ( No POST, not logged in, roles, ... )
        $this->onShouldIgnoreRequest($event);

        // And additionally those for the response
        // ( No 5xx, no 'no_cache' on route, cache kill_switch, ... )
        $event->add($this->isCacheableStatusCode($response));
        $event->add($this->isCacheableAccordingToDrupal($request, $response));
    }

    protected function isAuthenticated(Request $request): bool
    {
        return (bool) $request->attributes->get(
            EnrichRequest::AUTHENTICATED,
            true
        );
    }

    protected function getUserId(Request $request): int
    {
        return (int) $request->attributes->get(
            EnrichRequest::UID,
            0
        );
    }

    protected function getRoles(Request $request): array
    {
        return $request->attributes->get(
            EnrichRequest::ROLES,
            ['anonymous']
        );
    }

    protected function isCacheableMethod(Request $request): AccessResult
    {
        return AccessResult::forbiddenIf(
            !isset($this->cacheableMethods[$request->getMethod()]),
            'Method not cacheable'
        );
    }

    protected function authenticationCheck(Request $request): AccessResult
    {
        return AccessResult::forbiddenIf(
            $this->ignoreAuthenticatedUsers && $this->isAuthenticated($request),
            'Authenticated user'
        );
    }

    protected function roleCheck(Request $request): AccessResult
    {
        return AccessResult::forbiddenIf(
            !$this->ignoreAuthenticatedUsers
            && $this->ignoredRoles
            && array_intersect($this->ignoredRoles, $this->getRoles($request)),
            'Ignored role'
        );
    }

    protected function isNotAdmin(Request $request): AccessResult
    {
        return AccessResult::forbiddenIf(
            $this->getUserId($request) === 1,
            'Administrator'
        );
    }

    protected function isCacheableStatusCode(Response $response): AccessResult
    {
        return AccessResult::forbiddenIf(
            !isset($this->cacheableStatusCodes[$response->getStatusCode()]),
            'Non-cacheable status code'
        );
    }

    protected function isCacheableAccordingToDrupal(
        Request $request,
        Response $response
    ): AccessResult {
        $cacheable = $this->cacheResponsePolicy->check(
            $response,
            $request
        );

        // Don't cache if Drupal thinks it's a bad idea to cache.
        // The cacheResponsePolicy by default has a few rules:
        // - page_cache_kill_switch triggers when drupal_get_message is used
        // - page_cache_no_cache_routes looks for the 'no_cache' route option
        // - page_cache_no_server_error makes sure we don't cache server errors
        // ...
        return AccessResult::forbiddenIf(
            $cacheable === ResponsePolicyInterface::DENY,
            'Drupal says no'
        );
    }
}
