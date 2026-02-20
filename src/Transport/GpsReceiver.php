<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Transport;

use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient;
use JsonException;
use LogicException;
use PetitPress\GpsMessengerBundle\Transport\Stamp\AttributesStamp;
use PetitPress\GpsMessengerBundle\Transport\Stamp\GpsReceivedStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Transport\Receiver\KeepaliveReceiverInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Throwable;

/**
 * @author Ronald Marfoldi <ronald.marfoldi@petitpress.sk>
 */
final class GpsReceiver implements KeepaliveReceiverInterface
{
    public const DEFAULT_KEEPALIVE_SECONDS = 5;

    public function __construct(
        private PubSubClient $pubSubClient,
        private GpsConfigurationInterface $gpsConfiguration,
        private SerializerInterface $serializer
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @psalm-suppress InvalidReturnType
     */
    public function get(): iterable
    {
        try {
            $messages = $this->pubSubClient
                ->subscription($this->gpsConfiguration->getSubscriptionName())
                ->pull($this->gpsConfiguration->getSubscriptionPullOptions());

            foreach ($messages as $message) {
                yield $this->createEnvelopeFromPubSubMessage($message);
            }
        } catch (Throwable $exception) {
            throw new TransportException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function ack(Envelope $envelope): void
    {
        try {
            $gpsReceivedStamp = $this->getGpsReceivedStamp($envelope);

            $this->pubSubClient
                ->subscription($this->gpsConfiguration->getSubscriptionName())
                ->acknowledge($gpsReceivedStamp->getGpsMessage())
            ;
        } catch (Throwable $exception) {
            throw new TransportException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * Called when handling the message failed and allows to warn PUB/SUB not to wait the ack.
     * After warning PUB/SUB, it will try to redeliver the message according to set up retry policy.
     *
     * @throws TransportException If there is an issue communicating with the transport
     *
     * @see https://cloud.google.com/pubsub/docs/reference/rest/v1/projects.subscriptions#RetryPolicy
     */
    public function reject(Envelope $envelope): void
    {
        try {
            $gpsReceivedStamp = $this->getGpsReceivedStamp($envelope);

            $subscription = $this->pubSubClient->subscription($this->gpsConfiguration->getSubscriptionName());

            if ($this->gpsConfiguration->shouldUseMessengerRetry()) {
                $subscription->acknowledge($gpsReceivedStamp->getGpsMessage());
            } else {
                $subscription->modifyAckDeadline($gpsReceivedStamp->getGpsMessage(), 0);
            }
        } catch (Throwable $exception) {
            throw new TransportException($exception->getMessage(), 0, $exception);
        }
    }

    private function getGpsReceivedStamp(Envelope $envelope): GpsReceivedStamp
    {
        $gpsReceivedStamp = $envelope->last(GpsReceivedStamp::class);
        if ($gpsReceivedStamp instanceof GpsReceivedStamp) {
            return $gpsReceivedStamp;
        }

        throw new LogicException('No GpsReceivedStamp found on the Envelope.');
    }

    /**
     * Creates Symfony Envelope from Google Pub/Sub Message.
     * It adds stamp with received native Google Pub/Sub message.
     */
    private function createEnvelopeFromPubSubMessage(Message $message): Envelope
    {
        try {
            /** @var array<string, mixed> $rawData */
            $rawData = json_decode($message->data(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new MessageDecodingFailedException($exception->getMessage(), 0, $exception);
        }

        /** @var array{body: string, headers?: array<string, string>} $rawData */
        $envelope = $this->serializer->decode($rawData)->with(new GpsReceivedStamp($message));

        $attributes = $message->attributes();
        if ($attributes !== []) {
            $envelope = $envelope->with(new AttributesStamp($attributes));
        }

        return $envelope;
    }

    public function keepalive(Envelope $envelope, ?int $seconds = null): void
    {
        try {
            $gpsReceivedStamp = $this->getGpsReceivedStamp($envelope);

            $this->pubSubClient
                ->subscription($this->gpsConfiguration->getSubscriptionName())
                ->modifyAckDeadline($gpsReceivedStamp->getGpsMessage(), $seconds ?? self::DEFAULT_KEEPALIVE_SECONDS)
            ;
        } catch (Throwable $exception) {
            throw new TransportException($exception->getMessage(), 0, $exception);
        }
    }
}
