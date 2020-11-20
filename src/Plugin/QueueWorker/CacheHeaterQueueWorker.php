<?php

namespace Drupal\wmpage_cache\Plugin\QueueWorker;

use Drupal\Core\Annotation\QueueWorker;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @QueueWorker(
 *     id = \Drupal\wmpage_cache\Plugin\QueueWorker\CacheHeaterQueueWorker::ID,
 *     title = @Translation("Hit a certain path to warm up the page cache."),
 *     cron = {"time" : 30}
 * )
 */
class CacheHeaterQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface
{
    public const ID = 'wmpage_cache.heater';

    /** @var Client */
    protected $client;
    /** @var string */
    protected $hostName;

    public static function create(
        ContainerInterface $container,
        array $configuration,
        $pluginId, $pluginDefinition
    ) {
        $instance = new static($configuration, $pluginId, $pluginDefinition);
        $instance->client = $container->get('wmpage_cache.heater.client');
        $instance->hostName = $container->getParameter('wmpage_cache.heater.host_name');

        return $instance;
    }

    public function processItem($uri)
    {
        try {
            $this->client->get($uri, [
                'headers' => [
                    // @see https://github.com/guzzle/guzzle/issues/1297
                    'Host' => $this->hostName,
                ],
            ]);
        } catch (GuzzleException $e) {
            watchdog_exception('wmpage_cache.heater', $e);
        }
    }
}
