# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2026-10-26
### Added
- Drupal 11 and 12 support.

### Removed
- `wmpage_cache_update_8001()`. It called `drupal_install_schema()`, which Drupal 10 removed. A site that never ran it
  has to run its updates with an earlier release first; `hook_update_last_removed()` makes Drupal say so.
- The obsolete `core: 8.x` key in the info file.
- Support for Drupal before 11.4 and PHP before 8.2. The module now requires Drupal `^11.4 || ^12` and PHP 8.2 or
  higher.

## [1.0.0] - 2024-01-07
### Added
- Add Drupal 10 support
- Add Last-Modified and ETag headers

### Removed
- Remove Drupal 8 support

### Fixed
- Fix `ValidationEvent` not being dispatched

## [1.0.0-alpha4] - 2023-10-30
### Fixed
- Mark http responses with status codes `300`, `301`, `302` and `410` cacheable
- Don't cache node preview responses
- Don't cache responses of the [drupal/preview_link](https://www.drupal.org/project/preview_link) module

## [1.0.0-alpha3] - 2023-07-19
### Added
- Support `stale-while-revalidate` and `stale-if-error` cache control header
    - [https://developer.fastly.com/learning/concepts/stale](http://web.archive.org/web/20230719193134/https://developer.fastly.com/learning/concepts/stale/)

## [1.0.0-alpha2] - 2022-07-05

Avoid null notice on strlen.

## [1.0.0-alpha1] - 2022-06-28
First tagged version.
