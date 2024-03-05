**Lightweight mocking library for [PSR-18 HTTP Client](https://www.php-fig.org/psr/psr-18/) implementations, tailor-made to help you create test suites that are easier to write and maintain.**

[![codecov](https://img.shields.io/codecov/c/github/psr-mock/http-client-implementation)](https://codecov.io/gh/psr-mock/http-client-implementation) [![packagist](https://img.shields.io/packagist/dt/psr-mock/http-client-implementation)](https://packagist.org/packages/psr-mock/http-client-implementation) ![status](https://img.shields.io/github/checks-status/psr-mock/http-client-implementation/1.x) ![license](https://img.shields.io/github/license/psr-mock/http-client-implementation)

This library is primarily intended for use in libraries like SDKs that consume PSR implementations without requiring hard dependencies on specific libraries. The library mocks a real-world implementation and strictly adheres to PSR specifications, enabling you to create test suites that are easier to write and maintain. The library also exposes a robust developer API to help you debug and fix test failures substantially faster.

This package is part of the [PSR Mock](https://github.com/psr-mock) suite of mocking libraries.

## Requirements

-   PHP 8.1+
-   Composer 2.0+

## Installation

```bash
composer require --dev psr-mock/http-client-implementation
```

Only install this library as a development dependency (`--dev`).
It's not built for use in production environments.

---

This library is not produced or endorsed by, or otherwise affiliated with, the PHP-FIG.
