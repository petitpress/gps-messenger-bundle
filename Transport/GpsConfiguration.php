<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Transport;

/**
 * @author Ronald Marfoldi <ronald.marfoldi@petitpress.sk>
 */
final class GpsConfiguration implements GpsConfigurationInterface
{
    private string $topicName;
    private string $subscriptionName;
    private array $clientConfig;
    private array $topicOptions;
    private array $subscriptionOptions;
    private array $subscriptionPullOptions;

    public function __construct(
        string $queueName,
        string $subscriptionName,
        array $clientConfig,
        array $topicOptions,
        array $subscriptionOptions,
        array $subscriptionPullOptions
    ) {
        $this->topicName = $queueName;
        $this->subscriptionName = $subscriptionName;
        $this->clientConfig = $clientConfig;
        $this->topicOptions = $topicOptions;
        $this->subscriptionOptions = $subscriptionOptions;
        $this->subscriptionPullOptions = $subscriptionPullOptions;
    }

    public function getTopicName(): string
    {
        return $this->topicName;
    }

    public function getSubscriptionName(): string
    {
        return $this->subscriptionName;
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

    public function getSubscriptionPullOptions(): array
    {
        return $this->subscriptionPullOptions;
    }
}
