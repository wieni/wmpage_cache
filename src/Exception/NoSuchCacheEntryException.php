<?php

namespace Drupal\wmpage_cache\Exception;

class NoSuchCacheEntryException extends \RuntimeException
{
    public function __construct($id, $message = '')
    {
        parent::__construct(
            sprintf(
                '%sNo cache entry found for "%s"',
                $message ? $message . ' => ' : '',
                $id
            )
        );
    }
}
