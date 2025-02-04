<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Tests\Transport;

use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Topic;
use PetitPress\GpsMessengerBundle\Transport\GpsConfigurationInterface;
use PetitPress\GpsMessengerBundle\Transport\GpsSender;
use PetitPress\GpsMessengerBundle\Transport\Stamp\AttributesStamp;
use PetitPress\GpsMessengerBundle\Transport\Stamp\OrderingKeyStamp;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * @author Mickael Prévôt <mickael.prevot@ext.adeo.com>
 * @author Ronald Marfoldi <ronald.marfoldi@petitpress.sk>
 */
class GpsSenderTest extends TestCase
{
    private const ORDERED_KEY = 'ordered-key';
    private const TOPIC_NAME = 'topic-name';

    /**
     * @var GpsConfigurationInterface&MockObject
     */
    private MockObject $gpsConfigurationMock;

    /**
     * @var PubSubClient&MockObject
     */
    private MockObject $pubSubClientMock;

    /**
     * @var SerializerInterface&MockObject
     */
    private MockObject $serializerMock;

    /**
     * @var Topic&MockObject
     */
    private MockObject $topicMock;

    private GpsSender $gpsSender;

    protected function setUp(): void
    {
        $this->gpsConfigurationMock = $this->createMock(GpsConfigurationInterface::class);
        $this->pubSubClientMock = $this->createMock(PubSubClient::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);
        $this->topicMock = $this->createMock(Topic::class);

        $this->gpsSender = new GpsSender(
            $this->pubSubClientMock,
            $this->gpsConfigurationMock,
            $this->serializerMock,
        );
    }

    public function testItDoesNotPublishIfTheLastStampIsOfTypeRedelivery(): void
    {
        $envelope = EnvelopeFactory::create(new RedeliveryStamp(0));
        $envelopeArray = ['body' => []];

        $this->serializerMock
            ->expects(static::once())
            ->method('encode')
            ->with($envelope)
            ->willReturn($envelopeArray)
        ;

        $this->pubSubClientMock
            ->expects(static::never())
            ->method('topic')
        ;

        self::assertSame($envelope, $this->gpsSender->send($envelope));
    }

    public function testItPublishesWithOrderingKey(): void
    {
        $envelope = EnvelopeFactory::create(new OrderingKeyStamp(self::ORDERED_KEY));
        $envelopeArray = ['body' => []];

        $this->serializerMock
            ->expects(static::once())
            ->method('encode')
            ->with($envelope)
            ->willReturn($envelopeArray)
        ;

        $this->gpsConfigurationMock
            ->expects(static::once())
            ->method('getTopicName')
            ->willReturn(self::TOPIC_NAME)
        ;

        $this->topicMock
            ->expects(static::once())
            ->method('publish')
            ->with(new Message(['data' => json_encode($envelopeArray), 'orderingKey' => self::ORDERED_KEY]))
        ;

        $this->pubSubClientMock
            ->expects(static::once())
            ->method('topic')
            ->with(self::TOPIC_NAME)
            ->willReturn($this->topicMock);

        self::assertSame($envelope, $this->gpsSender->send($envelope));
    }

    public function testItPublishesWithoutOrderingKey(): void
    {
        $envelope = EnvelopeFactory::create();
        $envelopeArray = ['body' => []];

        $this->serializerMock
            ->expects(static::once())
            ->method('encode')
            ->with($envelope)
            ->willReturn($envelopeArray)
        ;

        $this->gpsConfigurationMock
            ->expects(static::once())
            ->method('getTopicName')
            ->willReturn(self::TOPIC_NAME)
        ;

        $this->topicMock
            ->expects(static::once())
            ->method('publish')
            ->with(new Message(['data' => json_encode($envelopeArray), 'orderingKey' => null]))
        ;

        $this->pubSubClientMock
            ->expects(static::once())
            ->method('topic')
            ->with(self::TOPIC_NAME)
            ->willReturn($this->topicMock)
        ;

        self::assertSame($envelope, $this->gpsSender->send($envelope));
    }

    public function testItPublishesWithAttributes(): void
    {
        $attributes = ['foo' => 'bar'];
        $envelope = EnvelopeFactory::create(new AttributesStamp($attributes));
        $envelopeArray = ['body' => []];

        $this->serializerMock
            ->expects(static::once())
            ->method('encode')
            ->with($envelope)
            ->willReturn($envelopeArray)
        ;

        $this->gpsConfigurationMock
            ->expects(static::once())
            ->method('getTopicName')
            ->willReturn(self::TOPIC_NAME)
        ;

        $this->topicMock
            ->expects(static::once())
            ->method('publish')
            ->with(new Message([
                'data' => json_encode($envelopeArray),
                'attributes' => $attributes,
            ]))
        ;

        $this->pubSubClientMock
            ->expects(static::once())
            ->method('topic')
            ->with(self::TOPIC_NAME)
            ->willReturn($this->topicMock);

        self::assertSame($envelope, $this->gpsSender->send($envelope));
    }
}
