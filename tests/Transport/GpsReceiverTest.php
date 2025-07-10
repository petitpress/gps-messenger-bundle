<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Tests\Transport;

use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Subscription;
use PetitPress\GpsMessengerBundle\Transport\EncodingStrategy;
use PetitPress\GpsMessengerBundle\Transport\GpsConfigurationInterface;
use PetitPress\GpsMessengerBundle\Transport\GpsReceiver;
use PetitPress\GpsMessengerBundle\Transport\Stamp\GpsReceivedStamp;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * @author Mickael Prévôt <mickael.prevot@ext.adeo.com>
 * @author Ronald Marfoldi <ronald.marfoldi@petitpress.sk>
 */
class GpsReceiverTest extends TestCase
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

    private GpsReceiver $gpsReceiver;
    /**
     * @var SerializerInterface&MockObject
     */
    private MockObject $serializerMock;

    protected function setUp(): void
    {
        $this->gpsConfigurationMock = $this->createMock(GpsConfigurationInterface::class);
        $this->pubSubClientMock = $this->createMock(PubSubClient::class);
        $this->subscriptionMock = $this->createMock(Subscription::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);

        $this->gpsReceiver = new GpsReceiver(
            $this->pubSubClientMock,
            $this->gpsConfigurationMock,
            $this->serializerMock,
            EncodingStrategy::Wrapped,
        );
    }

    public function testItRejects(): void
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
            ->with($gpsMessage, 0)
        ;

        $this->pubSubClientMock
            ->expects(static::once())
            ->method('subscription')
            ->with(self::SUBSCRIPTION_NAME)
            ->willReturn($this->subscriptionMock)
        ;

        $this->gpsReceiver->reject(
            EnvelopeFactory::create(new GpsReceivedStamp($gpsMessage))
        );
    }

    #[DataProvider('wrappedEncodingStrategies')]
    public function testGetWrapped(EncodingStrategy $encodingStrategy): void
    {
        $gpsReceiver = new GpsReceiver(
            $this->pubSubClientMock,
            $this->gpsConfigurationMock,
            $this->serializerMock,
            $encodingStrategy,
        );

        $gpsMessage = new Message(['data' => '{"body":"foo"}']);

        $this->gpsConfigurationMock
            ->expects(static::once())
            ->method('getSubscriptionName')
            ->willReturn(self::SUBSCRIPTION_NAME)
        ;

        $this->gpsConfigurationMock
            ->expects(static::once())
            ->method('getSubscriptionPullOptions')
            ->willReturn([])
        ;

        $this->pubSubClientMock
            ->expects(static::once())
            ->method('subscription')
            ->with(self::SUBSCRIPTION_NAME)
            ->willReturn($this->subscriptionMock)
        ;

        $this->subscriptionMock
            ->expects(static::once())
            ->method('pull')
            ->with([])
            ->willReturn([$gpsMessage])
        ;

        $gpsEnvelope = new Envelope($gpsMessage);
        $gpsEnvelopeWithStamp = $gpsEnvelope->with(new GpsReceivedStamp($gpsMessage));
        $this->serializerMock
            ->expects(static::once())
            ->method('decode')
            ->with(['body' => 'foo'])
            ->willReturn($gpsEnvelope)
        ;

        $envelopes = $gpsReceiver->get();
        $i = 0;
        foreach ($envelopes as $envelope) {
            self::assertEquals($gpsEnvelopeWithStamp, $envelope);
            $i++;
        }

        self::assertSame(1, $i);
    }


    public function testGetFlat(): void
    {
        $gpsReceiver = new GpsReceiver(
            $this->pubSubClientMock,
            $this->gpsConfigurationMock,
            $this->serializerMock,
            EncodingStrategy::Flat,
        );

        $gpsMessage = new Message(['data' => '{"body":"foo"}']);

        $this->gpsConfigurationMock
            ->expects(static::once())
            ->method('getSubscriptionName')
            ->willReturn(self::SUBSCRIPTION_NAME)
        ;

        $this->gpsConfigurationMock
            ->expects(static::once())
            ->method('getSubscriptionPullOptions')
            ->willReturn([])
        ;

        $this->pubSubClientMock
            ->expects(static::once())
            ->method('subscription')
            ->with(self::SUBSCRIPTION_NAME)
            ->willReturn($this->subscriptionMock)
        ;

        $this->subscriptionMock
            ->expects(static::once())
            ->method('pull')
            ->with([])
            ->willReturn([$gpsMessage])
        ;

        $gpsEnvelope = new Envelope($gpsMessage);
        $gpsEnvelopeWithStamp = $gpsEnvelope->with(new GpsReceivedStamp($gpsMessage));
        $this->serializerMock
            ->expects(static::once())
            ->method('decode')
            ->with(['body' => '{"body":"foo"}', 'headers' => []])
            ->willReturn($gpsEnvelope)
        ;

        $envelopes = $gpsReceiver->get();
        $i = 0;
        foreach ($envelopes as $envelope) {
            self::assertEquals($gpsEnvelopeWithStamp, $envelope);
            $i++;
        }

        self::assertSame(1, $i);
    }

    /**
     * @return list<list<EncodingStrategy>>
     */
    public static function wrappedEncodingStrategies(): array
    {
        return [
            [EncodingStrategy::Wrapped],
            [EncodingStrategy::Hybrid],
        ];
    }

    public function testItThrowsAnExceptionInsteadOfRejecting(): void
    {
        $this->expectException(TransportException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No GpsReceivedStamp found on the Envelope.');

        $this->gpsReceiver->reject(EnvelopeFactory::create());
    }
}
