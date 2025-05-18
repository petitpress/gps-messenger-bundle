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
    private bool   $useMessengerRetry;

    /**
     * @var array<string, mixed>
     */
    private array  $clientConfig;

    /**
     * @var array<string, mixed>
     */
    private array  $topicOptions;

    /**
     * @var array<string, mixed>
     */
    private array  $subscriptionOptions;

    /**
     * @var array<string, mixed>
     */
    private array  $subscriptionPullOptions;

    /**
     * @param array<string, mixed>  $clientConfig
     * @param array<string, mixed>  $topicOptions
     * @param array<string, mixed>  $subscriptionOptions
     * @param array<string, mixed>  $subscriptionPullOptions
     */
    public function __construct(
        string $topicName,
        bool $topicCreationEnabled,
        string $subscriptionName,
        bool $subscriptionCreationEnabled,
        bool $useMessengerRetry,
        array $clientConfig,
        array $topicOptions,
        array $subscriptionOptions,
        array $subscriptionPullOptions
    ) {
        $this->topicName = $topicName;
        $this->topicCreationEnabled = $topicCreationEnabled;
        $this->subscriptionName = $subscriptionName;
        $this->subscriptionCreationEnabled = $subscriptionCreationEnabled;
        $this->useMessengerRetry = $useMessengerRetry;
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


    public function shouldUseMessengerRetry(): bool
    {
        return $this->useMessengerRetry;
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
