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

    public function __construct(Message $gpsMessage)
    {
        $this->gpsMessage = $gpsMessage;
    }

    public function getGpsMessage(): Message
    {
        return $this->gpsMessage;
    }
}