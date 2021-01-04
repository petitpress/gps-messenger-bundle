<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Tests\Transport;

use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Subscription;
use PetitPress\GpsMessengerBundle\Transport\GpsConfigurationInterface;
use PetitPress\GpsMessengerBundle\Transport\GpsReceiver;
use PetitPress\GpsMessengerBundle\Transport\Stamp\GpsReceivedStamp;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * @author Mickael Prévôt <mickael.prevot@ext.adeo.com>
 */
class GpsReceiverTest extends TestCase
{
    use ProphecyTrait;

    private const SUBSCRIPTION_NAME = 'subscription-name';

    private ObjectProphecy $gpsConfigurationProphecy;
    private GpsReceiver $gpsReceiver;
    private ObjectProphecy $pubSubClientProphecy;
    private ObjectProphecy $serializerProphecy;
    private ObjectProphecy $subscriptionProphecy;

    protected function setUp(): void
    {
        $this->gpsConfigurationProphecy = $this->prophesize(GpsConfigurationInterface::class);
        $this->pubSubClientProphecy = $this->prophesize(PubSubClient::class);
        $this->serializerProphecy = $this->prophesize(SerializerInterface::class);
        $this->subscriptionProphecy = $this->prophesize(Subscription::class);

        $this->gpsReceiver = new GpsReceiver(
            $this->pubSubClientProphecy->reveal(),
            $this->gpsConfigurationProphecy->reveal(),
            $this->serializerProphecy->reveal(),
        );
    }

    public function testItRejects(): void
    {
        $gpsMessage = $this->prophesize(Message::class);

        $this->gpsConfigurationProphecy->getSubscriptionName()->willReturn(self::SUBSCRIPTION_NAME)->shouldBeCalledOnce();

        $this->subscriptionProphecy->modifyAckDeadline($gpsMessage->reveal(), 0)->shouldBeCalledOnce();

        $this->pubSubClientProphecy->subscription(self::SUBSCRIPTION_NAME)->willReturn($this->subscriptionProphecy->reveal())->shouldBeCalledOnce();

        $this->gpsReceiver->reject(EnvelopeFactory::create(new GpsReceivedStamp($gpsMessage->reveal())));
    }

    public function testItThrowsAnExceptionInsteadOfRejecting(): void
    {
        $this->expectException(TransportException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No GpsReceivedStamp found on the Envelope.');

        $this->gpsReceiver->reject(EnvelopeFactory::create());
    }
}
