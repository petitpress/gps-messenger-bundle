<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Tests\Transport;

use Generator;
use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Subscription;
use Google\Cloud\PubSub\Topic;
use PetitPress\GpsMessengerBundle\Transport\EncodingStrategy;
use PetitPress\GpsMessengerBundle\Transport\GpsConfigurationInterface;
use PetitPress\GpsMessengerBundle\Transport\GpsReceiver;
use PetitPress\GpsMessengerBundle\Transport\GpsSender;
use PetitPress\GpsMessengerBundle\Transport\GpsTransport;
use PetitPress\GpsMessengerBundle\Transport\Stamp\GpsReceivedStamp;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class GpsTransportTest extends TestCase
{
    /**
     */
    private GpsTransport $gpsTransport;

    /**
     * @var PubSubClient&MockObject
     */
    private MockObject $pubSubClient;

    /**
     * @var GpsConfigurationInterface&MockObject
     */
    private MockObject $gpsConfiguration;

    /**
     * @var SerializerInterface&MockObject
     */
    private MockObject $serializerMock;

    protected function setUp(): void
    {
        $this->pubSubClient = $this->createMock(PubSubClient::class);
        $this->gpsConfiguration = $this->createMock(GpsConfigurationInterface::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);

        $this->gpsTransport = new GpsTransport(
            $this->pubSubClient,
            $this->gpsConfiguration,
            $this->serializerMock,
            EncodingStrategy::Wrapped,
        );
    }

    public function testGet(): void
    {
        static::assertInstanceOf(Generator::class, $this->gpsTransport->get());
    }

    public function testAck(): void
    {
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('No GpsReceivedStamp found on the Envelope.');

        $this->gpsTransport->ack(new Envelope(new stdClass()));
    }

    public function testReject(): void
    {
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('No GpsReceivedStamp found on the Envelope.');

        $this->gpsTransport->reject(new Envelope(new stdClass()));
    }

    public function testSend(): void
    {
        $transportConfiguration = new GpsReceivedStamp(
            new Message(
                [
                    'data' => '{}',
                    'messageId' => 'messageId',
                    'publishTime' => time(),
                    'attributes' => [],
                    'orderingKey' => null,
                ]
            )
        );

        $message = new stdClass();
        $message->prop = 'test';

        $envelope = new Envelope($message, [$transportConfiguration]);

        $this->serializerMock
            ->expects(static::once())
            ->method('encode')
            ->willReturn(
                [
                    'body' => '{}',
                    'headers' => [],
                ]
            );

        $topicMock = $this->createMock(Topic::class);
        $topicMock
            ->expects(static::once())
            ->method('publish');

        $this->pubSubClient
            ->expects(static::once())
            ->method('topic')
            ->willReturn($topicMock);

        $finalEnvelop = $this->gpsTransport->send($envelope);

        static::assertSame($message, $finalEnvelop->getMessage());
    }

    public function testSetup(): void
    {
        $subscription = 'test';

        $this->gpsConfiguration
            ->expects(static::atLeast(2))
            ->method('getTopicName')
            ->willReturn($subscription);

        $this->gpsConfiguration
            ->expects(static::atLeast(2))
            ->method('getSubscriptionName')
            ->willReturn($subscription);

        $this->gpsConfiguration
            ->expects(static::atLeast(1))
            ->method('isTopicCreationEnabled')
            ->willReturn(true);

        $this->gpsConfiguration
            ->expects(static::atLeast(1))
            ->method('isSubscriptionCreationEnabled')
            ->willReturn(true);

        $topicMock = $this->createMock(Topic::class);
        $topicMock
            ->expects(static::once())
            ->method('exists')
            ->willReturn(false);

        $subscriptionMock = $this->createMock(Subscription::class);
        $subscriptionMock
            ->expects(static::once())
            ->method('exists')
            ->willReturn(false);

        $topicMock
            ->expects(static::once())
            ->method('subscription')
            ->willReturn($subscriptionMock);

        $this->pubSubClient
            ->expects(static::once())
            ->method('topic')
            ->willReturn($topicMock);

        $this->pubSubClient
            ->expects(static::once())
            ->method('createTopic')
            ->willReturn($topicMock);

        $this->gpsTransport->setup();
    }

    public function testGetReceiver(): void
    {
        static::assertInstanceOf(GpsReceiver::class, $this->gpsTransport->getReceiver());
    }

    public function testGetSender(): void
    {
        static::assertInstanceOf(GpsSender::class, $this->gpsTransport->getSender());
    }
}
