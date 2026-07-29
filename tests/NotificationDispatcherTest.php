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

namespace Milpa\Notifications\Tests;

use PHPUnit\Framework\TestCase;
use Milpa\Notifications\NotificationDispatcher;
use Milpa\Notifications\Contracts\NotificationChannelInterface;
use Milpa\Notifications\Contracts\NotificationPayloadInterface;
use Psr\Log\LoggerInterface;

class NotificationDispatcherTest extends TestCase
{
    private NotificationDispatcher $dispatcher;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->dispatcher = new NotificationDispatcher($this->logger);
    }

    private function createMockChannel(string $name, bool $supports = true, bool $notifySuccess = true): NotificationChannelInterface
    {
        $channel = $this->createMock(NotificationChannelInterface::class);
        $channel->method('getChannelName')->willReturn($name);
        $channel->method('supports')->willReturn($supports);
        $channel->method('notify')->willReturn($notifySuccess);
        return $channel;
    }

    private function createMockPayload(string $type = 'message'): NotificationPayloadInterface
    {
        $payload = $this->createMock(NotificationPayloadInterface::class);
        $payload->method('getType')->willReturn($type);
        return $payload;
    }

    public function testAddChannel(): void
    {
        $channel = $this->createMockChannel('telegram');

        $result = $this->dispatcher->addChannel($channel);

        $this->assertSame($this->dispatcher, $result);
        $this->assertTrue($this->dispatcher->hasChannel('telegram'));
    }

    public function testRemoveChannel(): void
    {
        $channel = $this->createMockChannel('telegram');
        $this->dispatcher->addChannel($channel);

        $result = $this->dispatcher->removeChannel('telegram');

        $this->assertSame($this->dispatcher, $result);
        $this->assertFalse($this->dispatcher->hasChannel('telegram'));
    }

    public function testGetChannels(): void
    {
        $telegram = $this->createMockChannel('telegram');
        $pusher = $this->createMockChannel('pusher');

        $this->dispatcher->addChannel($telegram);
        $this->dispatcher->addChannel($pusher);

        $channels = $this->dispatcher->getChannels();

        $this->assertCount(2, $channels);
        $this->assertArrayHasKey('telegram', $channels);
        $this->assertArrayHasKey('pusher', $channels);
    }

    public function testHasChannel(): void
    {
        $channel = $this->createMockChannel('telegram');
        $this->dispatcher->addChannel($channel);

        $this->assertTrue($this->dispatcher->hasChannel('telegram'));
        $this->assertFalse($this->dispatcher->hasChannel('nonexistent'));
    }

    public function testDispatchSuccess(): void
    {
        $channel = $this->createMockChannel('telegram');
        $this->dispatcher->addChannel($channel);

        $payload = $this->createMockPayload();
        $recipients = ['telegram:123456'];

        $results = $this->dispatcher->dispatch($payload, $recipients);

        $this->assertArrayHasKey('telegram:123456', $results);
        $this->assertTrue($results['telegram:123456']);
    }

    public function testDispatchToMultipleRecipients(): void
    {
        $telegram = $this->createMockChannel('telegram');
        $pusher = $this->createMockChannel('pusher');

        $this->dispatcher->addChannel($telegram);
        $this->dispatcher->addChannel($pusher);

        $payload = $this->createMockPayload();
        $recipients = ['telegram:123', 'pusher:room1', 'telegram:456'];

        $results = $this->dispatcher->dispatch($payload, $recipients);

        $this->assertCount(3, $results);
        $this->assertTrue($results['telegram:123']);
        $this->assertTrue($results['pusher:room1']);
        $this->assertTrue($results['telegram:456']);
    }

    public function testDispatchInvalidRecipientFormat(): void
    {
        $payload = $this->createMockPayload();
        $recipients = ['invalid_format'];

        $this->logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('Invalid recipient format'));

        $results = $this->dispatcher->dispatch($payload, $recipients);

        $this->assertFalse($results['invalid_format']);
    }

    public function testDispatchChannelNotFound(): void
    {
        $payload = $this->createMockPayload();
        $recipients = ['unknown:123'];

        $this->logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('Channel not found'));

        $results = $this->dispatcher->dispatch($payload, $recipients);

        $this->assertFalse($results['unknown:123']);
    }

    public function testDispatchChannelDoesNotSupportType(): void
    {
        $channel = $this->createMockChannel('telegram', supports: false);
        $this->dispatcher->addChannel($channel);

        $payload = $this->createMockPayload('unsupported_type');
        $recipients = ['telegram:123'];

        $results = $this->dispatcher->dispatch($payload, $recipients);

        $this->assertFalse($results['telegram:123']);
    }

    public function testDispatchNotifyFails(): void
    {
        $channel = $this->createMockChannel('telegram', supports: true, notifySuccess: false);
        $this->dispatcher->addChannel($channel);

        $payload = $this->createMockPayload();
        $recipients = ['telegram:123'];

        $results = $this->dispatcher->dispatch($payload, $recipients);

        $this->assertFalse($results['telegram:123']);
    }

    public function testDispatchNotifyThrowsException(): void
    {
        $channel = $this->createMock(NotificationChannelInterface::class);
        $channel->method('getChannelName')->willReturn('telegram');
        $channel->method('supports')->willReturn(true);
        $channel->method('notify')->willThrowException(new \RuntimeException('Connection failed'));

        $this->dispatcher->addChannel($channel);

        $payload = $this->createMockPayload();
        $recipients = ['telegram:123'];

        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Error sending to'));

        $results = $this->dispatcher->dispatch($payload, $recipients);

        $this->assertFalse($results['telegram:123']);
    }

    public function testBroadcast(): void
    {
        $telegram = $this->createMockChannel('telegram');
        $pusher = $this->createMockChannel('pusher');

        $this->dispatcher->addChannel($telegram);
        $this->dispatcher->addChannel($pusher);

        $payload = $this->createMockPayload();

        $results = $this->dispatcher->broadcast($payload, 'user123');

        $this->assertCount(2, $results);
        $this->assertTrue($results['telegram']);
        $this->assertTrue($results['pusher']);
    }

    public function testBroadcastSkipsUnsupportedChannels(): void
    {
        $telegram = $this->createMockChannel('telegram', supports: true);
        $pusher = $this->createMockChannel('pusher', supports: false);

        $this->dispatcher->addChannel($telegram);
        $this->dispatcher->addChannel($pusher);

        $payload = $this->createMockPayload();

        $results = $this->dispatcher->broadcast($payload, 'user123');

        $this->assertCount(1, $results);
        $this->assertArrayHasKey('telegram', $results);
        $this->assertArrayNotHasKey('pusher', $results);
    }

    public function testBroadcastHandlesExceptions(): void
    {
        $telegram = $this->createMock(NotificationChannelInterface::class);
        $telegram->method('getChannelName')->willReturn('telegram');
        $telegram->method('supports')->willReturn(true);
        $telegram->method('notify')->willThrowException(new \RuntimeException('Failed'));

        $this->dispatcher->addChannel($telegram);

        $payload = $this->createMockPayload();

        $results = $this->dispatcher->broadcast($payload, 'user123');

        $this->assertFalse($results['telegram']);
    }

    public function testDispatcherWithoutLogger(): void
    {
        $dispatcher = new NotificationDispatcher();
        $channel = $this->createMockChannel('telegram');
        $dispatcher->addChannel($channel);

        $payload = $this->createMockPayload();
        $recipients = ['telegram:123'];

        $results = $dispatcher->dispatch($payload, $recipients);

        $this->assertTrue($results['telegram:123']);
    }
}
