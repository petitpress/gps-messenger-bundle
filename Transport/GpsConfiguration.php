<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Transport;

/**
 * @author Ronald Marfoldi <ronald.marfoldi@petitpress.sk>
 */
final class GpsConfiguration implements GpsConfigurationInterface
{
    private string $topicName;
    private bool   $topicCreationEnabled;
    private string $subscriptionName;
    private bool   $subscriptionCreationEnabled;
    private array  $clientConfig;
    private array  $topicOptions;
    private array  $subscriptionOptions;
    private array  $subscriptionPullOptions;

    public function __construct(
        string $queueName,
        bool $topicCreationEnabled,
        string $subscriptionName,
        bool $subscriptionCreationEnabled,
        array $clientConfig,
        array $topicOptions,
        array $subscriptionOptions,
        array $subscriptionPullOptions
    ) {
        $this->topicName = $queueName;
        $this->topicCreationEnabled = $topicCreationEnabled;
        $this->subscriptionName = $subscriptionName;
        $this->subscriptionCreationEnabled = $subscriptionCreationEnabled;
        $this->clientConfig = $clientConfig;
        $this->topicOptions = $topicOptions;
        $this->subscriptionOptions = $subscriptionOptions;
        $this->subscriptionPullOptions = $subscriptionPullOptions;
    }

    public function getTopicName(): string
    {
        return $this->topicName;
    }

    public function isTopicCreationEnabled(): bool
    {
        return $this->topicCreationEnabled;
    }

    public function getSubscriptionName(): string
    {
        return $this->subscriptionName;
    }

    public function isSubscriptionCreationEnabled(): bool
    {
        return $this->subscriptionCreationEnabled;
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
