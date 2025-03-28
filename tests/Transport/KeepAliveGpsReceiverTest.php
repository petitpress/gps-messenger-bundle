<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Tests\Transport;

use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Subscription;
use PetitPress\GpsMessengerBundle\Transport\GpsConfigurationInterface;
use PetitPress\GpsMessengerBundle\Transport\KeepAliveGpsReceiver;
use PetitPress\GpsMessengerBundle\Transport\Stamp\GpsReceivedStamp;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class KeepAliveGpsReceiverTest extends TestCase
{
    private const SUBSCRIPTION_NAME = 'subscription-name';

    /**
     * @var GpsConfigurationInterface&MockObject
     */
    private MockObject $gpsConfigurationMock;

    /**
     * @var PubSubClient&MockObject
     */
    private MockObject $pubSubClientMock;

    /**
     * @var Subscription&MockObject
     */
    private MockObject $subscriptionMock;

    private KeepAliveGpsReceiver $gpsReceiver;

    protected function setUp(): void
    {
        $this->gpsConfigurationMock = $this->createMock(GpsConfigurationInterface::class);
        $this->pubSubClientMock = $this->createMock(PubSubClient::class);
        $this->subscriptionMock = $this->createMock(Subscription::class);
        /** @var SerializerInterface&MockObject $serializerMock */
        $serializerMock = $this->createMock(SerializerInterface::class);

        $this->gpsReceiver = new KeepAliveGpsReceiver(
            $this->pubSubClientMock,
            $this->gpsConfigurationMock,
            $serializerMock,
        );
    }

    public function testItKeepsAliveMessage(): void
    {
        $gpsMessage = new Message(['data' => '']);

        $this->gpsConfigurationMock
            ->expects(static::once())
            ->method('getSubscriptionName')
            ->willReturn(self::SUBSCRIPTION_NAME)
        ;

        $this->subscriptionMock
            ->expects(static::once())
            ->method('modifyAckDeadline')
            ->with($gpsMessage, KeepAliveGpsReceiver::DEFAULT_KEEPALIVE_SECONDS)
        ;

        $this->pubSubClientMock
            ->expects(static::once())
            ->method('subscription')
            ->with(self::SUBSCRIPTION_NAME)
            ->willReturn($this->subscriptionMock)
        ;

        $this->gpsReceiver->keepalive(
            EnvelopeFactory::create(new GpsReceivedStamp($gpsMessage))
        );
    }

    public function testItThrowsAnExceptionOnErrorWhenKeepingAlive(): void
    {
        $this->expectException(TransportException::class);
        $this->expectExceptionCode(0);

        $this->gpsReceiver->keepalive(
            EnvelopeFactory::create(new GpsReceivedStamp(new Message(['data' => ''])))
        );
    }
}
