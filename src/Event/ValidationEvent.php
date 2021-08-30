<?php

namespace Drupal\wmpage_cache\Event;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\wmpage_cache\Validation\ValidationResult;
use Drupal\wmpage_cache\Validation\ValidationResultInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidationEvent extends Event
{
    /** @var Request */
    protected $request;
    /** @var Response|null */
    protected $response;
    /** @var string */
    protected $resultClass;

    /** @var ValidationResultInterface */
    protected $result;
    /** @var AccessResult[] */
    protected $results = [];

    public function __construct(Request $request, ?Response $response = null, ?string $resultClass = null)
    {
        $this->resultClass = $resultClass ?: ValidationResult::class;
        $this->request = $request;
        $this->response = $response;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function add(AccessResult $result): void
    {
        $this->result = null;
        $this->results[] = $result;
    }

    /** Check whether or not this request or response should be cached. */
    public function result(): ValidationResultInterface
    {
        if (isset($this->result)) {
            return $this->result;
        }

        return $this->result = new $this->resultClass(
            $this->processAccessResults($this->results)
        );
    }

    protected function processAccessResults(array $access): AccessResultInterface
    {
        // No results means no opinion.
        if (empty($access)) {
            return AccessResult::neutral();
        }

        /** @var AccessResultInterface $result */
        $result = array_shift($access);
        foreach ($access as $other) {
            $result = $result->orIf($other);
        }

        return $result;
    }
}
