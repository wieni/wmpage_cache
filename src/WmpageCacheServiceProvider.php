<?php

namespace Drupal\wmpage_cache;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;

class WmpageCacheServiceProvider implements ServiceModifierInterface
{
    public function alter(ContainerBuilder $container): void
    {
        if ($container->getParameter('wmpage_cache.tags')) {
            $container->removeDefinition('http_middleware.page_cache');
        }
    }
}
