<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Tests\Transport;

use Generator;
use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Subscription;
use Google\Cloud\PubSub\Topic;
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
    private GpsTransport $subject;

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

        $this->subject = new GpsTransport(
            $this->pubSubClient,
            $this->gpsConfiguration,
            $this->serializerMock
        );
    }

    public function testGet(): void
    {
        $this->assertInstanceOf(Generator::class, $this->subject->get());
    }

    public function testAck(): void
    {
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('No GpsReceivedStamp found on the Envelope.');

        $this->subject->ack(new Envelope(new stdClass()));
    }

    public function testReject(): void
    {
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('No GpsReceivedStamp found on the Envelope.');

        $this->subject->reject(new Envelope(new stdClass()));
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
            ->expects($this->once())
            ->method('encode')
            ->willReturn(
                [
                    'body' => '{}',
                    'headers' => [],
                ]
            );

        $topicMock = $this->createMock(Topic::class);
        $topicMock
            ->expects($this->once())
            ->method('publish');

        $this->pubSubClient
            ->expects($this->once())
            ->method('topic')
            ->willReturn($topicMock);

        $finalEnvelop = $this->subject->send($envelope);

        $this->assertInstanceOf(Envelope::class, $finalEnvelop);
    }

    public function testSetup()
    {
        $queue = 'test';

        $this->gpsConfiguration
            ->expects($this->atLeast(2))
            ->method('getTopicName')
            ->willReturn($queue);

        $this->gpsConfiguration
            ->expects($this->atLeast(2))
            ->method('getSubscriptionName')
            ->willReturn($queue);

        $topicMock = $this->createMock(Topic::class);
        $topicMock
            ->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        $subscriptionMock = $this->createMock(Subscription::class);
        $subscriptionMock
            ->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        $topicMock
            ->expects($this->once())
            ->method('subscription')
            ->willReturn($subscriptionMock);

        $this->pubSubClient
            ->expects($this->once())
            ->method('topic')
            ->willReturn($topicMock);

        $this->pubSubClient
            ->expects($this->once())
            ->method('createTopic')
            ->willReturn($topicMock);

        $this->subject->setup();
    }

    public function testGetReceiver()
    {
        $this->assertInstanceOf(GpsReceiver::class, $this->subject->getReceiver());
    }

    public function testGetSender()
    {
        $this->assertInstanceOf(GpsSender::class, $this->subject->getSender());
    }
}
