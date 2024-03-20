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

    public function isTopicEnabled(): bool;

    public function getSubscriptionName(): string;

    public function isSubscriptionEnabled(): bool;

    /**
     * @see PubSubClient constructor options
     */
    public function getClientConfig(): array;

    /**
     * @see Topic::create options
     */
    public function getTopicOptions(): array;

    /**
     * @see Subscription::create options
     */
    public function getSubscriptionOptions(): array;

    /**
     * @see Subscription::pull options
     */
    public function getSubscriptionPullOptions(): array;
}
