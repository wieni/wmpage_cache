Page Cache
======================

[![Latest Stable Version](https://poser.pugx.org/wieni/wmpage_cache/v/stable)](https://packagist.org/packages/wieni/wmpage_cache)
[![Total Downloads](https://poser.pugx.org/wieni/wmpage_cache/downloads)](https://packagist.org/packages/wieni/wmpage_cache)
[![License](https://poser.pugx.org/wieni/wmpage_cache/license)](https://packagist.org/packages/wieni/wmpage_cache)

> Caches pages for anonymous users, with more customisability than the default page cache module.

## Why?
_TODO_

## Installation

This package requires PHP 7.1 and Drupal 8 or higher. It can be
installed using Composer:

```bash
 composer require wieni/wmpage_cache
```

Patch: https://www.drupal.org/project/drupal/issues/2638686

## How does it work?
### Choosing a cache storage adapter

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


LogicException: Render context is empty, because render() was called outside of a renderRoot() or renderPlain() call. Use renderPlain()/renderRoot() or #lazy_builder/#pre_render instead
when using Dispatcher->dispatchTags: attach cache tags to relevant objects instead of using global state


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
