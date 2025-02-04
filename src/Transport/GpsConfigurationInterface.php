<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Transport;

use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Subscription;
use Google\Cloud\PubSub\Topic;

/**
 * @author Ronald Marfoldi <ronald.marfoldi@petitpress.sk>
 */
interface GpsConfigurationInterface
{
    public function getTopicName(): string;

    public function isTopicCreationEnabled(): bool;

    public function getSubscriptionName(): string;

    public function isSubscriptionCreationEnabled(): bool;

    /**
     * @see PubSubClient constructor options
     * @return array<string, mixed>
     */
    public function getClientConfig(): array;

    /**
     * @see Topic::create options
     * @return array<string, mixed>
     */
    public function getTopicOptions(): array;

    /**
     * @see Subscription::create options
     * @return array<string, mixed>
     */
    public function getSubscriptionOptions(): array;

    /**
     * @see Subscription::pull options
     * @return array<string, mixed>
     */
    public function getSubscriptionPullOptions(): array;
}
