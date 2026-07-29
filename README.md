<p align="center">
  <a href="https://github.com/getmilpa">
    <picture>
      <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/getmilpa/core/main/art/lockup/milpa-lockup-v-color-dark.svg">
      <img src="https://raw.githubusercontent.com/getmilpa/core/main/art/lockup/milpa-lockup-v-color-light.svg" alt="Milpa" width="300">
    </picture>
  </a>
</p>

# Milpa Notifications

> Channel-agnostic notification dispatch for the Milpa PHP framework — one payload, many channels,
> and a failing channel never takes the others down.

[![CI](https://github.com/getmilpa/notifications/actions/workflows/ci.yml/badge.svg)](https://github.com/getmilpa/notifications/actions/workflows/ci.yml)
[![Packagist](https://img.shields.io/packagist/v/milpa/notifications.svg)](https://packagist.org/packages/milpa/notifications)
[![PHP](https://img.shields.io/badge/php-%E2%89%A5%208.3-777bb4.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-Apache--2.0-blue.svg)](LICENSE)

Register channels (Telegram, Pushover, WebSocket, e-mail, …) and fan a single payload out to
recipients addressed as `channel:recipient` — or broadcast one payload to every channel. The package
owns the contracts and a default payload value object; the channels come from the host or its plugins.

Register notification channels (Telegram, Pushover, WebSocket, e-mail, …) and fan a single payload
out to recipients addressed as `channel:recipient` — or broadcast one payload to every channel. The
package owns the contracts (`NotificationChannelInterface`, `NotificationPayloadInterface`) and a
default payload value object; channels are supplied by the host or its plugins.

- **Zero framework coupling** — the only runtime dependency is [PSR-3](https://www.php-fig.org/psr/psr-3/)
  (`psr/log`) for optional logging. No ORM, no HTTP, no container.
- **Fail-soft** — a throwing or missing channel never aborts the dispatch; each recipient gets its own
  boolean result and the error is logged.

## Install

```bash
composer require milpa/notifications
```

## Usage

```php
use Milpa\Notifications\NotificationDispatcher;
use Milpa\Notifications\NotificationPayload;

$dispatcher = (new NotificationDispatcher($logger))
    ->addChannel($telegramChannel); // implements Milpa\Notifications\Contracts\NotificationChannelInterface

$payload = new NotificationPayload(type: 'alert', title: 'Deploy finished');

// Targeted: "channel:recipient" — routed to the matching channel.
$dispatcher->dispatch($payload, ['telegram:123456']);

// Broadcast: the same recipient id across every registered channel.
$dispatcher->broadcast($payload, '123456');
```

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md). Security reports go through
[SECURITY.md](SECURITY.md) — privately, via GitHub Security Advisories.

## License

[Apache-2.0](LICENSE) © Rodrigo Vicente - TeamX Agency.

---

Milpa is designed, built, and maintained by **[Rodrigo Vicente - TeamX Agency](https://teamx.agency/?utm_source=github&utm_medium=readme&utm_campaign=milpa&utm_content=notifications)**.

