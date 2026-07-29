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
use Milpa\Notifications\Contracts\NotificationPayloadInterface;
use Psr\Log\LoggerInterface;

/**
 * Dispatches notifications to multiple channels.
 *
 * Acts as a notification hub that can send the same notification
 * to multiple registered channels (Telegram, Pusher, WebSocket, etc).
 */
class NotificationDispatcher
{
    /** @var NotificationChannelInterface[] */
    private array $channels = [];

    private ?LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * Register a notification channel.
     */
    public function addChannel(NotificationChannelInterface $channel): self
    {
        $this->channels[$channel->getChannelName()] = $channel;
        return $this;
    }

    /**
     * Remove a channel by name.
     */
    public function removeChannel(string $channelName): self
    {
        unset($this->channels[$channelName]);
        return $this;
    }

    /**
     * Get all registered channels.
     *
     * @return NotificationChannelInterface[]
     */
    public function getChannels(): array
    {
        return $this->channels;
    }

    /**
     * Check if a channel is registered.
     */
    public function hasChannel(string $channelName): bool
    {
        return isset($this->channels[$channelName]);
    }

    /**
     * Dispatch a notification to specific recipients.
     *
     * Recipients format: "channel:recipient_id"
     * Example: ["telegram:123456", "pusher:room1", "websocket:user:42"]
     *
     * @param NotificationPayloadInterface $payload    The notification data
     * @param string[]                     $recipients Array of "channel:recipient" strings
     *
     * @return array<string, bool> Results per recipient
     */
    public function dispatch(NotificationPayloadInterface $payload, array $recipients): array
    {
        $results = [];

        foreach ($recipients as $recipient) {
            $parts = explode(':', $recipient, 2);
            if (count($parts) !== 2) {
                $this->logger?->warning("[NotificationDispatcher] Invalid recipient format: {$recipient}");
                $results[$recipient] = false;
                continue;
            }

            [$channelName, $recipientId] = $parts;

            if (!isset($this->channels[$channelName])) {
                $this->logger?->warning("[NotificationDispatcher] Channel not found: {$channelName}");
                $results[$recipient] = false;
                continue;
            }

            $channel = $this->channels[$channelName];

            if (!$channel->supports($payload->getType())) {
                $this->logger?->debug("[NotificationDispatcher] Channel {$channelName} doesn't support type: {$payload->getType()}");
                $results[$recipient] = false;
                continue;
            }

            try {
                $success = $channel->notify($recipientId, $payload);
                $results[$recipient] = $success;

                if ($success) {
                    $this->logger?->debug("[NotificationDispatcher] Sent to {$recipient}");
                } else {
                    $this->logger?->warning("[NotificationDispatcher] Failed to send to {$recipient}");
                }
            } catch (\Throwable $e) {
                $this->logger?->error("[NotificationDispatcher] Error sending to {$recipient}: " . $e->getMessage());
                $results[$recipient] = false;
            }
        }

        return $results;
    }

    /**
     * Broadcast a notification to all channels.
     *
     * @param NotificationPayloadInterface $payload     The notification data
     * @param string                       $recipientId The recipient ID (same for all channels)
     *
     * @return array<string, bool> Results per channel
     */
    public function broadcast(NotificationPayloadInterface $payload, string $recipientId): array
    {
        $results = [];

        foreach ($this->channels as $channelName => $channel) {
            if (!$channel->supports($payload->getType())) {
                continue;
            }

            try {
                $results[$channelName] = $channel->notify($recipientId, $payload);
            } catch (\Throwable $e) {
                $this->logger?->error("[NotificationDispatcher] Broadcast error on {$channelName}: " . $e->getMessage());
                $results[$channelName] = false;
            }
        }

        return $results;
    }
}
