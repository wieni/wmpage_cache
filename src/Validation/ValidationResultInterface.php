<?php

namespace Drupal\wmpage_cache\Validation;

interface ValidationResultInterface
{
    public function result(string $method): bool;
}
