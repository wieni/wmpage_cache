<?php

namespace Drupal\wmpage_cache\Validation;

use Drupal\Core\Access\AccessResultInterface;

abstract class ValidationResult implements ValidationResultInterface
{
    /** @var AccessResultInterface */
    protected $result;

    public function __construct(AccessResultInterface $result)
    {
        $this->result = $result;
    }

    public function result(string $method): bool
    {
        if (!is_callable([$this, $method])) {
            return false;
        }

        return (bool) $this->{$method}();
    }
}
