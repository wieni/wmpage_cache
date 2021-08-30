<?php

namespace Drupal\wmpage_cache\Event;

use Drupal\Core\Access\AccessResult;
use Drupal\wmpage_cache\Validation\ValidationResult;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidationEvent extends Event
{
    /** @var Request */
    protected $request;
    /** @var Response */
    protected $response;
    /** @var string */
    protected $resultClass;

    protected $result;
    protected $results = [];

    public function __construct(Request $request, ?Response $response = null, $resultClass = null)
    {
        $this->resultClass = $resultClass ?: ValidationResult::class;
        $this->request = $request;
        $this->response = $response;
    }

    /** @return Request */
    public function getRequest()
    {
        return $this->request;
    }

    /** @return Response */
    public function getResponse()
    {
        return $this->response;
    }

    public function add(AccessResult $result)
    {
        $this->result = null;
        $this->results[] = $result;
    }

    /**
     * Check whether or not this request or response should be cached.
     *
     * @return ValidationResult
     */
    public function result()
    {
        if (isset($this->result)) {
            return $this->result;
        }

        return $this->result = new $this->resultClass(
            $this->processAccessResults($this->results)
        );
    }

    protected function processAccessResults(array $access)
    {
        // No results means no opinion.
        if (empty($access)) {
            return AccessResult::neutral();
        }

        /** @var \Drupal\Core\Access\AccessResultInterface $result */
        $result = array_shift($access);
        foreach ($access as $other) {
            $result = $result->orIf($other);
        }
        return $result;
    }
}
