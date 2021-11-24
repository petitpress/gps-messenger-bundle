<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Transport;

use Google\Cloud\PubSub\PubSubClient;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * @author Ronald Marfoldi <ronald.marfoldi@petitpress.sk>
 */
final class GpsTransport implements TransportInterface
{
    private PubSubClient $pubSubClient;
    private GpsConfigurationInterface $gpsConfiguration;
    private SerializerInterface $serializer;
    private GpsReceiver $receiver;
    private GpsSender $sender;

    public function __construct(
        PubSubClient $pubSubClient,
        GpsConfigurationInterface $gpsConfiguration,
        SerializerInterface $serializer
    ) {
        $this->pubSubClient = $pubSubClient;
        $this->gpsConfiguration = $gpsConfiguration;
        $this->serializer = $serializer;
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

    public function getReceiver(): GpsReceiver
    {
        if (isset($this->receiver)) {
            return $this->receiver;
        }

        $this->receiver = new GpsReceiver($this->pubSubClient, $this->gpsConfiguration, $this->serializer);

        return $this->receiver;
    }

    public function getSender(): GpsSender
    {
        if (isset($this->sender)) {
            return $this->sender;
        }

        $this->sender = new GpsSender($this->pubSubClient, $this->gpsConfiguration, $this->serializer);

        return $this->sender;
    }
}