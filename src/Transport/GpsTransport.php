<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Transport;

use Google\Cloud\PubSub\PubSubClient;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Receiver\KeepaliveReceiverInterface;
use Symfony\Component\Messenger\Transport\Sender\SenderInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\SetupableTransportInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * @author Ronald Marfoldi <ronald.marfoldi@petitpress.sk>
 */
final class GpsTransport implements TransportInterface, KeepaliveReceiverInterface, SetupableTransportInterface
{
    private KeepaliveReceiverInterface $receiver;
    private SenderInterface $sender;

    public function __construct(
        private PubSubClient $pubSubClient,
        private GpsConfigurationInterface $gpsConfiguration,
        private SerializerInterface $serializer
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function get(): iterable
    {
        return $this->getReceiver()->get();
    }

    /**
     * {@inheritdoc}
     */
    public function ack(Envelope $envelope): void
    {
        $this->getReceiver()->ack($envelope);
    }

    /**
     * {@inheritdoc}
     */
    public function reject(Envelope $envelope): void
    {
        $this->getReceiver()->reject($envelope);
    }

    /**
     * {@inheritdoc}
     */
    public function send(Envelope $envelope): Envelope
    {
        return $this->getSender()->send($envelope);
    }

    public function getReceiver(): KeepaliveReceiverInterface
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        return $this->receiver ??= new GpsReceiver($this->pubSubClient, $this->gpsConfiguration, $this->serializer);
    }

    public function getSender(): SenderInterface
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        return $this->sender ??= new GpsSender($this->pubSubClient, $this->gpsConfiguration, $this->serializer);
    }

    public function setup(): void
    {
        $topic = $this->pubSubClient->topic($this->gpsConfiguration->getTopicName());

        if (true === $this->gpsConfiguration->isTopicCreationEnabled() && false === $topic->exists()) {
            $topic = $this->pubSubClient->createTopic(
                $this->gpsConfiguration->getTopicName(),
                $this->gpsConfiguration->getTopicOptions()
            );
        }

        $subscription = $topic->subscription($this->gpsConfiguration->getSubscriptionName());

        if (true === $this->gpsConfiguration->isSubscriptionCreationEnabled() && false === $subscription->exists()) {
            $subscriptionOptions = $this->normalizeSubscriptionOptions();
            $topic->subscribe(
                $this->gpsConfiguration->getSubscriptionName(),
                $subscriptionOptions
            );
        }
    }

    public function keepalive(Envelope $envelope, ?int $seconds = null): void
    {
        $this->getReceiver()->keepalive($envelope, $seconds);
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeSubscriptionOptions(): array
    {
        /** @var array{deadLetterPolicy?: array{deadLetterTopic?: string, maxDeliveryAttempts?: int}} $subscriptionOptions */
        $subscriptionOptions = $this->gpsConfiguration->getSubscriptionOptions();

        // normalize dead letter topic format
        if (! isset($subscriptionOptions['deadLetterPolicy']['deadLetterTopic'])) {
            return $subscriptionOptions;
        }

        $deadLetterTopic = $subscriptionOptions['deadLetterPolicy']['deadLetterTopic'];
        if (! str_starts_with($deadLetterTopic, 'projects/')) {
            $subscriptionOptions['deadLetterPolicy']['deadLetterTopic'] =
                $this->pubSubClient->topic($this->gpsConfiguration->getTopicName());
        }

        return $subscriptionOptions;
    }
}
