<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Transport\Stamp;

use Symfony\Component\Messenger\Stamp\NonSendableStampInterface;

/**
 * @author Ronald Marfoldi <ronald.marfoldi@petitpress.sk>
 */
final class OrderingKeyStamp implements NonSendableStampInterface
{
    private string $orderingKey;

    public function __construct(string $orderingKey)
    {
        $this->orderingKey = $orderingKey;
    }

    public function getOrderingKey(): string
    {
        return $this->orderingKey;
    }
}