<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Transport;

use Google\Cloud\PubSub\MessageBuilder;
use Google\Cloud\PubSub\PubSubClient;
use PetitPress\GpsMessengerBundle\Transport\Stamp\AttributesStamp;
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
    private EncodingStrategy $encodingStrategy;

    public function __construct(
        PubSubClient $pubSubClient,
        GpsConfigurationInterface $gpsConfiguration,
        SerializerInterface $serializer,
        EncodingStrategy $encodingStrategy,
    ) {
        $this->pubSubClient = $pubSubClient;
        $this->gpsConfiguration = $gpsConfiguration;
        $this->serializer = $serializer;
        $this->encodingStrategy = $encodingStrategy;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Envelope $envelope): Envelope
    {
        /** @var array{body: string, headers?: array<string, string>} $encodedMessage */
        $encodedMessage = $this->serializer->encode($envelope);

        if ($envelope->last(RedeliveryStamp::class) instanceof RedeliveryStamp) {
            // do not try to redeliver, message wasn't acknowledged, so let's Google Pub/Sub do its job with retry policy
            return $envelope;
        }

        $messageBuilder = $this->setMessage(new MessageBuilder(), $encodedMessage);

        $orderingKeyStamp = $envelope->last(OrderingKeyStamp::class);
        if ($orderingKeyStamp instanceof OrderingKeyStamp) {
            $messageBuilder = $messageBuilder->setOrderingKey($orderingKeyStamp->getOrderingKey());
        }

        $attributesStamp = $envelope->last(AttributesStamp::class);
        $stampAttributes = $attributesStamp instanceof AttributesStamp ? $attributesStamp->getAttributes() : [];
        $messageBuilder = $messageBuilder->setAttributes(
            $this->buildAttributes($encodedMessage['headers'] ?? [], $stampAttributes)
        );

        $this->pubSubClient
            ->topic($this->gpsConfiguration->getTopicName())
            ->publish($messageBuilder->build())
        ;

        return $envelope;
    }

    /**
     * @param array{body: string, headers?: array<string, string>} $encodedMessage
     */
    private function setMessage(MessageBuilder $messageBuilder, array $encodedMessage): MessageBuilder
    {
        if (EncodingStrategy::Flat === $this->encodingStrategy || EncodingStrategy::Hybrid === $this->encodingStrategy) {
            return $messageBuilder->setData($encodedMessage['body']);
        }

        try {
            return $messageBuilder->setData(json_encode($encodedMessage, JSON_THROW_ON_ERROR));
        } catch (\JsonException $exception) {
            throw new TransportException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * Computes the final Pub/Sub attribute set:
     *   - Wrapped: only AttributesStamp values are exposed (headers are inside the JSON body).
     *   - Flat/Hybrid: serializer headers + AttributesStamp values (stamp wins on collision),
     *     plus the reserved encoding-version attribute appended last so AttributesStamp can never override it.
     *
     * @param array<string, string> $headers
     * @param array<string, string> $stampAttributes
     *
     * @return array<string, string>
     */
    private function buildAttributes(array $headers, array $stampAttributes): array
    {
        if (EncodingStrategy::Wrapped === $this->encodingStrategy) {
            return $stampAttributes;
        }

        return array_merge(
            $headers,
            $stampAttributes,
            [EncodingStrategy::ENCODING_ATTRIBUTE => EncodingStrategy::ENCODING_VERSION],
        );
    }
}
