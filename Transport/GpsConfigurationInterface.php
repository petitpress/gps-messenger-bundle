<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Transport;

use Google\Cloud\PubSub\PubSubClient;

/**
 * @author Ronald Marfoldi <ronald.marfoldi@petitpress.sk>
 */
interface GpsConfigurationInterface
{
    public function getQueueName(): string;

    public function getSubscriptionName(): string;

    public function getMaxMessagesPull(): int;

    /**
     * @see PubSubClient constructor options
     */
    public function getClientConfig(): array;
}
