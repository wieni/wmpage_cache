<?php

namespace Drupal\wmpage_cache\Validation;

class CacheableResponseResult extends ValidationResult
{
    public const ALLOW_CACHED = 'allowCached';

    /** Returns true if nobody said it's forbidden to cache this. */
    public function allowCached(): bool
    {
        return !$this->result->isForbidden();
    }
}
