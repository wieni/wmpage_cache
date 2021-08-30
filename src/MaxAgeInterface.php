<?php

namespace Drupal\wmpage_cache;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface MaxAgeInterface
{
    /** @return array{'s-maxage': int, 'maxage': int, 'wm-s-maxage': int} */
    public function getMaxage(Request $request, Response $response): array;
}
