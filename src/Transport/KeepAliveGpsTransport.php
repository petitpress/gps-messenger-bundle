<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Transport;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Receiver\KeepaliveReceiverInterface;

/**
 * @author Damien Fernandes <damien.fernandes24@gmail.com>
 */
final class KeepAliveGpsTransport extends GpsTransport implements KeepaliveReceiverInterface
{
    public function keepalive(Envelope $envelope, ?int $seconds = null): void
    {
        $receiver = $this->getReceiver();
        if (! $receiver instanceof KeepaliveReceiverInterface) {
            throw new \LogicException(sprintf('Receiver of class %s must implement %s.', get_class($receiver), KeepaliveReceiverInterface::class));
        }
        $receiver->keepalive($envelope, $seconds);
    }
}
