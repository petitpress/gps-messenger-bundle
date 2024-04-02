<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Transport;

/**
 * @author Ronald Marfoldi <ronald.marfoldi@petitpress.sk>
 */
final class GpsConfiguration implements GpsConfigurationInterface
{
    private string $topicName;
    private bool   $createTopicIfNotExist;
    private string $subscriptionName;
    private bool   $createSubscriptionIfNotExist;
    private int $maxMessagesPull;
    private array $clientConfig;
    private array $topicOptions;
    private array $subscriptionOptions;

    public function __construct(
        string $queueName,
        bool $createTopicIfNotExist,
        string $subscriptionName,
        bool $createSubscriptionIfNotExist,
        int $maxMessagesPull,
        array $clientConfig,
        array $topicOptions,
        array $subscriptionOptions
    ) {
        $this->topicName = $queueName;
        $this->createTopicIfNotExist = $createTopicIfNotExist;
        $this->subscriptionName = $subscriptionName;
        $this->createSubscriptionIfNotExist = $createSubscriptionIfNotExist;
        $this->maxMessagesPull = $maxMessagesPull;
        $this->clientConfig = $clientConfig;
        $this->topicOptions = $topicOptions;
        $this->subscriptionOptions = $subscriptionOptions;
    }

    public function getTopicName(): string
    {
        return $this->topicName;
    }

    public function createTopicIfNotExist(): bool
    {
        return $this->createTopicIfNotExist;
    }

    public function getSubscriptionName(): string
    {
        return $this->subscriptionName;
    }

    public function createSubscriptionIfNotExist(): bool
    {
        return $this->createSubscriptionIfNotExist;
    }

    public function getMaxMessagesPull(): int
    {
        return $this->maxMessagesPull;
    }

    public function getClientConfig(): array
    {
        return $this->clientConfig;
    }

    public function getTopicOptions(): array
    {
        return $this->topicOptions;
    }

    public function getSubscriptionOptions(): array
    {
        return $this->subscriptionOptions;
    }
}
