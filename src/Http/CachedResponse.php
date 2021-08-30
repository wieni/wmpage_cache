<?php

namespace Drupal\wmpage_cache\Http;

use Symfony\Component\HttpFoundation\Response;

/**
 * CachedResponse implies that this response was already cached.
 */
class CachedResponse extends Response
{
}
