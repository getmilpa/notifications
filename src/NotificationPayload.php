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

use Milpa\Notifications\Contracts\NotificationPayloadInterface;

/**
 * Notification payload data transfer object.
 *
 * Contains all data needed for a notification to be rendered and sent. A default implementation of
 * {@see NotificationPayloadInterface} — channels may accept any implementation of the interface.
 */
class NotificationPayload implements NotificationPayloadInterface
{
    /**
     * @param string               $type     Notification type (e.g., 'action_result', 'alert', 'report')
     * @param string               $title    Notification title
     * @param array<string, mixed> $data     Notification data
     * @param string|null          $template Optional template name
     * @param array<string, mixed> $metadata Additional metadata
     */
    public function __construct(
        private string $type,
        private string $title,
        private array $data = [],
        private ?string $template = null,
        private array $metadata = []
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Create from array (useful for deserialization).
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'] ?? 'generic',
            title: $data['title'] ?? '',
            data: $data['data'] ?? [],
            template: $data['template'] ?? null,
            metadata: $data['metadata'] ?? []
        );
    }

    /**
     * Convert to array (useful for serialization).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
            'data' => $this->data,
            'template' => $this->template,
            'metadata' => $this->metadata,
        ];
    }
}
