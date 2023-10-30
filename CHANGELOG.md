# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
