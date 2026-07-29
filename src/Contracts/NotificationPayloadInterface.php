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
 * Interface for notification payloads.
 *
 * Defines the contract for notification data that can be sent through channels.
 */
interface NotificationPayloadInterface
{
    /**
     * Get the notification type.
     *
     * @return string Type identifier (e.g., 'action_result', 'alert', 'report')
     */
    public function getType(): string;

    /**
     * Get the notification title.
     *
     * @return string
     */
    public function getTitle(): string;

    /**
     * Get the notification data.
     *
     * @return array<string, mixed>
     */
    public function getData(): array;

    /**
     * Get optional template name for custom rendering.
     *
     * @return string|null
     */
    public function getTemplate(): ?string;

    /**
     * Get additional metadata.
     *
     * @return array<string, mixed>
     */
    public function getMetadata(): array;
}
