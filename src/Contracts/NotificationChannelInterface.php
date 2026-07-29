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

namespace Milpa\Notifications\Contracts;

/**
 * Interface for notification channels.
 *
 * Plugins implementing this interface can send notifications
 * to their respective channel (Telegram, Pusher, WebSocket, etc).
 */
interface NotificationChannelInterface
{
    /**
     * Send a notification through this channel.
     *
     * @param string                       $recipient Channel-specific recipient (e.g., chat_id for Telegram)
     * @param NotificationPayloadInterface $payload   The notification data
     *
     * @return bool True if sent successfully
     */
    public function notify(string $recipient, NotificationPayloadInterface $payload): bool;

    /**
     * Get the channel identifier.
     *
     * @return string Channel name (e.g., 'telegram', 'pusher', 'websocket')
     */
    public function getChannelName(): string;

    /**
     * Check if this channel supports the given payload type.
     *
     * @param string $payloadType The type of notification (e.g., 'action_result', 'alert')
     *
     * @return bool
     */
    public function supports(string $payloadType): bool;
}
