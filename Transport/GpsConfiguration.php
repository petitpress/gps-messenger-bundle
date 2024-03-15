<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Transport;

/**
 * @author Ronald Marfoldi <ronald.marfoldi@petitpress.sk>
 */
final class GpsConfiguration implements GpsConfigurationInterface
{
    private string $topicName;
    private bool   $isTopicEnabled;
    private string $subscriptionName;
    private bool   $isSubscriptionEnabled;
    private array  $clientConfig;
    private array  $topicOptions;
    private array  $subscriptionOptions;
    private array  $subscriptionPullOptions;

    public function __construct(
        string $queueName,
        bool   $isTopicEnabled,
        string $subscriptionName,
        bool   $isSubscriptionEnabled,
        array  $clientConfig,
        array  $topicOptions,
        array  $subscriptionOptions,
        array  $subscriptionPullOptions
    ) {
        $this->topicName = $queueName;
        $this->isTopicEnabled = $isTopicEnabled;
        $this->subscriptionName = $subscriptionName;
        $this->isSubscriptionEnabled = $isSubscriptionEnabled;
        $this->clientConfig = $clientConfig;
        $this->topicOptions = $topicOptions;
        $this->subscriptionOptions = $subscriptionOptions;
        $this->subscriptionPullOptions = $subscriptionPullOptions;
    }

    public function getTopicName(): string
    {
        return $this->topicName;
    }

    public function isTopicEnabled(): bool
    {
        return $this->isTopicEnabled;
    }

    public function getSubscriptionName(): string
    {
        return $this->subscriptionName;
    }

    public function isSubscriptionEnabled(): bool
    {
        return $this->isSubscriptionEnabled;
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
