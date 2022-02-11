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
    private array $clientConfig;

    public function __construct(
        string $queueName,
        string $subscriptionName,
        int $maxMessagesPull,
        array $clientConfig
    ) {
        $this->queueName = $queueName;
        $this->subscriptionName = $subscriptionName;
        $this->maxMessagesPull = $maxMessagesPull;
        $this->clientConfig = $clientConfig;
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

    public function getClientConfig(): array
    {
        return $this->clientConfig;
    }
}
