<?php

namespace Drupal\wmpage_cache\Validation;

class CacheableRequestResult extends ValidationResult
{
    public const ALLOW_FETCHING = 'allowCachedResponse';

    /**
     * Returns true if nobody says it's forbidden to fetch the cached version.
     *
     * @return bool
     */
    public function allowCachedResponse()
    {
        return $this->result->isForbidden() === false;
    }
}
