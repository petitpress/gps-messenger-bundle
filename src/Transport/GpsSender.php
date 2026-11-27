<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Transport;

use Google\Cloud\PubSub\MessageBuilder;
use Google\Cloud\PubSub\PubSubClient;
use PetitPress\GpsMessengerBundle\Transport\Stamp\AttributesStamp;
use PetitPress\GpsMessengerBundle\Transport\Stamp\GpsReceivedStamp;
use PetitPress\GpsMessengerBundle\Transport\Stamp\OrderingKeyStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Messenger\Transport\Sender\SenderInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * @author Ronald Marfoldi <ronald.marfoldi@petitpress.sk>
 */
final class GpsSender implements SenderInterface
{
    private PubSubClient $pubSubClient;
    private GpsConfigurationInterface $gpsConfiguration;
    private SerializerInterface $serializer;
    private DroppedRetryCopies $droppedRetryCopies;

    public function __construct(
        PubSubClient $pubSubClient,
        GpsConfigurationInterface $gpsConfiguration,
        SerializerInterface $serializer,
        ?DroppedRetryCopies $droppedRetryCopies = null
    ) {
        $this->pubSubClient = $pubSubClient;
        $this->gpsConfiguration = $gpsConfiguration;
        $this->serializer = $serializer;
        $this->droppedRetryCopies = $droppedRetryCopies ?? new DroppedRetryCopies();
    }

    /**
     * {@inheritdoc}
     */
    public function send(Envelope $envelope): Envelope
    {
        $encodedMessage = $this->serializer->encode($envelope);

        $messageBuilder = new MessageBuilder();
        try {
            $messageBuilder = $messageBuilder->setData(json_encode($encodedMessage, JSON_THROW_ON_ERROR));
        } catch (\JsonException $exception) {
            throw new TransportException($exception->getMessage(), 0, $exception);
        }

        $redeliveryStamp = $envelope->last(RedeliveryStamp::class);
        if ($redeliveryStamp instanceof RedeliveryStamp) {
            // do not try to redeliver, message wasn't acknowledged, so let's Google Pub/Sub do its job with retry policy
            $gpsReceivedStamp = $envelope->last(GpsReceivedStamp::class);
            if ($gpsReceivedStamp instanceof GpsReceivedStamp) {
                $this->droppedRetryCopies->add($gpsReceivedStamp->getGpsMessage());
            }

            return $envelope;
        }

        $orderingKeyStamp = $envelope->last(OrderingKeyStamp::class);
        if ($orderingKeyStamp instanceof OrderingKeyStamp) {
            $messageBuilder = $messageBuilder->setOrderingKey($orderingKeyStamp->getOrderingKey());
        }

        $attributesStamp = $envelope->last(AttributesStamp::class);
        if ($attributesStamp instanceof AttributesStamp) {
            $messageBuilder = $messageBuilder->setAttributes($attributesStamp->getAttributes());
        }

        $this->pubSubClient
            ->topic($this->gpsConfiguration->getTopicName())
            ->publish($messageBuilder->build())
        ;

        return $envelope;
    }
}
