<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Tests\Transport;

use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Topic;
use PetitPress\GpsMessengerBundle\Transport\GpsConfigurationInterface;
use PetitPress\GpsMessengerBundle\Transport\GpsSender;
use PetitPress\GpsMessengerBundle\Transport\Stamp\OrderingKeyStamp;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Messenger\Stamp\StampInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * @author Mickael Prévôt <mickael.prevot@ext.adeo.com>
 */
class GpsSenderTest extends TestCase
{
    use ProphecyTrait;

    private const ORDERED_KEY = 'ordered-key';
    private const TOPIC_NAME = 'topic-name';

    private ObjectProphecy $gpsConfigurationProphecy;
    private GpsSender $gpsSender;
    private ObjectProphecy $pubSubClientProphecy;
    private ObjectProphecy $serializerProphecy;
    private ObjectProphecy $topicProphecy;

    protected function setUp(): void
    {
        $this->gpsConfigurationProphecy = $this->prophesize(GpsConfigurationInterface::class);
        $this->pubSubClientProphecy = $this->prophesize(PubSubClient::class);
        $this->serializerProphecy = $this->prophesize(SerializerInterface::class);
        $this->topicProphecy = $this->prophesize(Topic::class);

        $this->gpsSender = new GpsSender(
            $this->pubSubClientProphecy->reveal(),
            $this->gpsConfigurationProphecy->reveal(),
            $this->serializerProphecy->reveal(),
        );
    }

    public function testItDoesNotPublishIfTheLastStampIsOfTyeRedelivery(): void
    {
        $envelope = EnvelopeFactory::create(new RedeliveryStamp(0));
        $envelopeArray = ['body' => []];

        $this->serializerProphecy->encode($envelope)->willReturn($envelopeArray)->shouldBeCalledOnce();

        $this->pubSubClientProphecy->topic(Argument::any())->shouldNotBeCalled();

        self::assertSame($envelope, $this->gpsSender->send($envelope));
    }

    public function testItPublishesWithOrderingKey(): void
    {
        $envelope = EnvelopeFactory::create(new OrderingKeyStamp(self::ORDERED_KEY));
        $envelopeArray = ['body' => []];

        $this->serializerProphecy->encode($envelope)->willReturn($envelopeArray)->shouldBeCalledOnce();

        $this->gpsConfigurationProphecy->getQueueName()->willReturn(self::TOPIC_NAME)->shouldBeCalledOnce();

        $this->pubSubClientProphecy->topic(self::TOPIC_NAME)->willReturn($this->topicProphecy->reveal())->shouldBeCalledOnce();

        $this->topicProphecy->publish(Argument::allOf(
            new Argument\Token\ObjectStateToken('data', \json_encode($envelopeArray)),
            new Argument\Token\ObjectStateToken('orderingKey', self::ORDERED_KEY),
        ))->shouldBeCalledOnce();

        self::assertSame($envelope, $this->gpsSender->send($envelope));
    }

    public function testItPublishesWithoutOrderingKey(): void
    {
        $envelope = EnvelopeFactory::create();
        $envelopeArray = ['body' => []];

        $this->serializerProphecy->encode($envelope)->willReturn($envelopeArray)->shouldBeCalledOnce();

        $this->gpsConfigurationProphecy->getQueueName()->willReturn(self::TOPIC_NAME)->shouldBeCalledOnce();

        $this->pubSubClientProphecy->topic(self::TOPIC_NAME)->willReturn($this->topicProphecy->reveal())->shouldBeCalledOnce();

        $this->topicProphecy->publish(Argument::allOf(
            new Argument\Token\ObjectStateToken('data', \json_encode($envelopeArray)),
            new Argument\Token\ObjectStateToken('orderingKey', null),
        ))->shouldBeCalledOnce();

        self::assertSame($envelope, $this->gpsSender->send($envelope));
    }
}
