<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Transport;

/**
 * @author Ronald Marfoldi <ronald.marfoldi@petitpress.sk>
 */
interface GpsConfigurationInterface
{
    public function getQueueName(): string;

    public function getSubscriptionName(): string;

    public function getMaxMessagesPull(): int;
}