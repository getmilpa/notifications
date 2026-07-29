<?php

/**
 * This file is part of Milpa Notifications — the channel-agnostic notification dispatch of the Milpa PHP framework.
 *
 * (c) Rodrigo Vicente - TeamX Agency — https://teamx.agency <hola@teamx.agency>
 *
 * @license Apache-2.0
 *
 * @link    https://github.com/getmilpa/notifications
 */

declare(strict_types=1);

namespace Milpa\Notifications;

use Milpa\Notifications\Contracts\NotificationChannelInterface;

/**
 * Registry for notification channels.
 *
 * Allows plugins to register their channels so they can be retrieved dynamically
 * by other services.
 */
class NotificationChannelRegistry
{
    /** @var array<string, NotificationChannelInterface> */
    private array $channels = [];

    /**
     * Register a notification channel.
     *
     * @param string                       $name    The channel name (e.g., 'telegram', 'pushover')
     * @param NotificationChannelInterface $channel The channel instance
     */
    public function register(string $name, NotificationChannelInterface $channel): void
    {
        $this->channels[$name] = $channel;
    }

    /**
     * Get a registered channel by name.
     *
     * @param string $name
     *
     * @return NotificationChannelInterface|null
     */
    public function get(string $name): ?NotificationChannelInterface
    {
        return $this->channels[$name] ?? null;
    }

    /**
     * Check if a channel is registered.
     */
    public function has(string $name): bool
    {
        return isset($this->channels[$name]);
    }

    /**
     * Get all registered channels.
     *
     * @return array<string, NotificationChannelInterface>
     */
    public function getAll(): array
    {
        return $this->channels;
    }
}
