# SessionManager

[![CI](https://github.com/echo-fusion/sessionmanager/actions/workflows/ci.yml/badge.svg)](https://github.com/echo-fusion/sessionmanager/actions/workflows/ci.yml) [![Packagist](https://img.shields.io/packagist/v/echo-fusion/sessionmanager.svg)](https://packagist.org/packages/echo-fusion/sessionmanager) [![PHP](https://img.shields.io/badge/php-8.1%20%7C%208.2%20%7C%208.3-777bb4.svg)](https://www.php.net) [![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

**SessionManager** provides session handling for the EchoFusion framework: a session backed by PHP's native sessions, an in-memory session for tests and CLI code, flash messages, and a PSR-15 middleware that starts the session.

Both sessions implement `EchoFusion\Contracts\SessionManager\SessionInterface` from [echo-fusion/contracts](https://github.com/echo-fusion/contracts), so other packages can depend on the interface only.

> Part of [EchoFusion](https://github.com/echo-fusion), a modular PHP framework built from small packages.

## Installation

```bash
composer require echo-fusion/sessionmanager
```

## Requirements

* PHP 8.1, 8.2 or 8.3

## Usage

### Native session

```php
use EchoFusion\SessionManager\NativeSession;
use EchoFusion\SessionManager\SessionOptions;

$session = new NativeSession(new SessionOptions(name: 'app_sid', lifetime: 7200));
$session->start();

$session->put('user', 42);
$session->get('user');            // 42
$session->get('missing', 'n/a');  // 'n/a'
$session->has('user');            // true
$session->forget('user');

$session->regenerate();           // new session id, e.g. after login or logout
$session->save();                 // write and close
```

`SessionOptions` defaults are secure: the cookie is `Secure`, `HttpOnly` and `SameSite=Lax`. For local development over plain HTTP pass `secure: false`.

Calling `get`, `put`, `has`, `forget`, `regenerate`, `flash` or `getFlash` before `start()` throws `EchoFusion\SessionManager\Exceptions\SessionException`, as does starting a session twice or after output has been sent.

### Flash messages

```php
$session->flash('errors', ['Email is required']);

// on the next request
$session->getFlash('errors');  // ['Email is required']
$session->getFlash('errors');  // []  (read once)
```

Flash data is stored under an internal key, so it never collides with your own keys.

### In-memory session

`ArraySession` has the same behaviour without touching `$_SESSION`, which makes it handy in tests:

```php
use EchoFusion\SessionManager\ArraySession;

$session = new ArraySession();
$session->start();
```

### Middleware

`StartSessionMiddleware` is a PSR-15 middleware that starts the session (if it is not already active) before the rest of the pipeline runs:

```php
use EchoFusion\SessionManager\Middleware\StartSessionMiddleware;

$middleware = new StartSessionMiddleware($session);
```

## Testing

```bash
composer test
```

## Credits

- [Amir Shadanfar](https://github.com/amir-shadanfar)

## License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.
