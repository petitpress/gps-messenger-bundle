<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Transport;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Transport\Receiver\KeepaliveReceiverInterface;
use Throwable;

final class KeepAliveGpsReceiver extends GpsReceiver implements KeepaliveReceiverInterface
{
    public const DEFAULT_KEEPALIVE_SECONDS = 10;

    public function keepalive(Envelope $envelope, ?int $seconds = null): void
    {
        try {
            $gpsReceivedStamp = $this->getGpsReceivedStamp($envelope);

            $this->pubSubClient
                ->subscription($this->gpsConfiguration->getSubscriptionName())
                ->modifyAckDeadline($gpsReceivedStamp->getGpsMessage(), $seconds ?? self::DEFAULT_KEEPALIVE_SECONDS)
            ;
        } catch (Throwable $exception) {
            throw new TransportException($exception->getMessage(), 0, $exception);
        }
    }
}
