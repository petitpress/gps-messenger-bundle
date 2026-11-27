<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Transport;

use Google\Cloud\PubSub\Message;

/**
 * Received Pub/Sub messages whose retry copy the sender dropped, so that Pub/Sub redelivers them.
 *
 * Symfony Messenger 6.4.49 and 7.4.22 acknowledge a message they sent for retry (symfony/symfony#67038) and pass no
 * sign of the retry to the receiver, so the sender tells the receiver through this list.
 *
 * @author Ronald Marfoldi <ronald.marfoldi@petitpress.sk>
 */
final class DroppedRetryCopies
{
    /**
     * @var array<string, true>
     */
    private array $ackIds = [];

    public function add(Message $message): void
    {
        $this->ackIds[$message->ackId()] = true;
    }

    public function remove(Message $message): bool
    {
        if (! isset($this->ackIds[$message->ackId()])) {
            return false;
        }

        unset($this->ackIds[$message->ackId()]);

        return true;
    }
}
