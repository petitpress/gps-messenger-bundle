<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Transport\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * @author Ignacio Visedo <naxo8628@gmail.com>
 */
final class GpsReceiverOptionsStamp implements StampInterface
{
    public function __construct(private array  $subscriptionInfo)
    {
    }

    public function getSubscriptionInfo(): array
    {
        return $this->subscriptionInfo;
    }


}
