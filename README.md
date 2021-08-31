Page Cache
======================

[![Latest Stable Version](https://poser.pugx.org/wieni/wmpage_cache/v/stable)](https://packagist.org/packages/wieni/wmpage_cache)
[![Total Downloads](https://poser.pugx.org/wieni/wmpage_cache/downloads)](https://packagist.org/packages/wieni/wmpage_cache)
[![License](https://poser.pugx.org/wieni/wmpage_cache/license)](https://packagist.org/packages/wieni/wmpage_cache)

> Caches pages for anonymous users, with more customisability than the default page cache module.

## Why?
- Provides more configurable options than the default page cache module
- Allows you to choose between multiple cache storages (database, [Redis](https://github.com/wieni/wmpage_cache_redis),
  [Flysystem](https://github.com/wieni/wmpage_cache_flysystem)) and cache invalidators/purgers (default, 
  [Cloudfront](https://github.com/wieni/wmpage_cache_cloudfront))

## Installation

This package requires PHP 7.1 and Drupal 8 or higher. It can be
installed using Composer:

```bash
 composer require wieni/wmpage_cache
```

To prevent unnecessary early rendering issues when creating custom controllers, a patch from the following issue should
be included:

[#2638686: Exception in EarlyRenderingControllerWrapperSubscriber is a DX nightmare, remove it](https://www.drupal.org/project/drupal/issues/2638686)

## How does it work?
### Configuring the module
Settings can be changed through container parameters. Check [`wmpage_cache.services.yml`](wmpage_cache.services.yml) for 
a list of settings, what they do and their default values.

### About cacheability metadata
Just like the [Internal Page Cache](https://www.drupal.org/docs/administering-a-drupal-site/internal-page-cache) & 
[Dynamic Page Cache](https://www.drupal.org/docs/8/core/modules/dynamic-page-cache/overview) core modules, this module 
uses cacheability metadata (cache tags, cache contexts and max-age) to determine the cacheability of a request and to 
make sure cached pages are invalidated when necessary.

### Adding cacheable metadata from a controller
There are a bunch of different things you can return in a controller and cacheable metadata can be included in pretty
much every one of them.

#### Render arrays
Cacheability metadata can be included in a render array under the `#cache` key. For more information, please refer to 
the [official documentation](https://www.drupal.org/docs/8/api/render-api/cacheability-of-render-arrays).

#### Response objects
Cacheable `Response` objects have to implement `CacheableResponseInterface` and can use the 
`CacheableResponseTrait`. For more information, please refer to the [official documentation](https://www.drupal.org/docs/8/api/responses/cacheableresponseinterface).

#### wmcontroller `ViewBuilder`
When rendering a Twig template, eg. by using the wmcontroller `ViewBuilder`, all cacheable metadata of parameters that 
are passed to the template are automatically included.

### Adding cacheable metadata from a Twig extension
If a Twig extension is returning information that will be used in a Twig template, without going through the Drupal 
render system, any cacheability metadata will be lost. That's why it's better to dispatch cacheability metadata in the
logic of the Twig extension. You can do this by attaching the metadata to an empty render array and rendering it:

```php
$build = [];
(new CacheableMetadata())
    ->addCacheableDependency($entity)
    ->applyTo($build);

$this->renderer->render($build);
```

In case this Twig extension is called often, this can impact performance. Another option is to collect all metadata 
until the end of page rendering, and attach it once in a `hook_page_attachments` implementation.

## Changelog
All notable changes to this project will be documented in the
[CHANGELOG](CHANGELOG.md) file.

## Security
If you discover any security-related issues, please email
[security@wieni.be](mailto:security@wieni.be) instead of using the issue
tracker.

## License
Distributed under the MIT License. See the [LICENSE](LICENSE) file
for more information.
