<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Transport\Stamp;

use Google\Cloud\PubSub\Message;
use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * @author Ronald Marfoldi <ronald.marfoldi@petitpress.sk>
 */
final class GpsReceivedStamp implements StampInterface
{
    private Message $gpsMessage;
    private ?int $maxDeliveryAttempts = null;
    private ?string $deadLetterTopic = null;

    public function __construct(Message $gpsMessage, array $subcriptionInfo)
    {
        $this->gpsMessage = $gpsMessage;
        $this->maxDeliveryAttempts = $subcriptionInfo['deadLetterPolicy']['maxDeliveryAttempts'] ?? null;
        $this->deadLetterTopic = $subcriptionInfo['deadLetterPolicy']['deadLetterTopic'] ?? null;
    }

    public function getGpsMessage(): Message
    {
        return $this->gpsMessage;
    }

    public function getMaxDeliveryAttempts(): ?int
    {
        return $this->maxDeliveryAttempts;
    }

    public function getDeadLetterTopic(): ?string
    {
        return $this->deadLetterTopic;
    }
}
