<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Transport;

/**
 * @author Ronald Marfoldi <ronald.marfoldi@petitpress.sk>
 */
final class GpsConfiguration implements GpsConfigurationInterface
{
    private string $queueName;
    private string $subscriptionName;
    private int $maxMessagesPull;

    public function __construct(string $queueName, string $subscriptionName, int $maxMessagesPull)
    {
        $this->queueName = $queueName;
        $this->subscriptionName = $subscriptionName;
        $this->maxMessagesPull = $maxMessagesPull;
    }

    public function getQueueName(): string
    {
        return $this->queueName;
    }

    public function getSubscriptionName(): string
    {
        return $this->subscriptionName;
    }

    public function getMaxMessagesPull(): int
    {
        return $this->maxMessagesPull;
    }
}