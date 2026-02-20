<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Tests\Transport;

use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Subscription;
use PetitPress\GpsMessengerBundle\Tests\Stub\DTO\StubDto;
use PetitPress\GpsMessengerBundle\Transport\GpsConfigurationInterface;
use PetitPress\GpsMessengerBundle\Transport\GpsReceiver;
use PetitPress\GpsMessengerBundle\Transport\Stamp\AttributesStamp;
use PetitPress\GpsMessengerBundle\Transport\Stamp\GpsReceivedStamp;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
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

    private SerializerInterface $serializer;

    private GpsReceiver $gpsReceiver;

    protected function setUp(): void
    {
        $this->gpsConfigurationMock = $this->createMock(GpsConfigurationInterface::class);
        $this->pubSubClientMock = $this->createMock(PubSubClient::class);
        $this->subscriptionMock = $this->createMock(Subscription::class);
        $this->serializer = Serializer::create();

        $this->gpsReceiver = new GpsReceiver(
            $this->pubSubClientMock,
            $this->gpsConfigurationMock,
            $this->serializer,
        );
    }

    public function testItGets(): void
    {
        $body = new StubDto('property-value-1', 'property-value-2');
        $headers = ['type' => $body::class];
        $attributes = ['attr-1' => 'val-1', 'attr-2' => 'val-2'];
        $gpsMessage = new Message(
            [
                'data' => json_encode(['headers' => $headers, 'body' => json_encode($body)]),
                'attributes' => $attributes,
            ]
        );

        $this->gpsConfigurationMock
            ->expects(static::once())
            ->method('getSubscriptionName')
            ->willReturn(self::SUBSCRIPTION_NAME);

        $this->subscriptionMock
            ->expects(static::once())
            ->method('pull')
            ->willReturn([$gpsMessage]);

        $this->pubSubClientMock
            ->expects(static::once())
            ->method('subscription')
            ->with(self::SUBSCRIPTION_NAME)
            ->willReturn($this->subscriptionMock);

        /** @var Envelope[] $envelopes */
        $envelopes = iterator_to_array($this->gpsReceiver->get());
        static::assertCount(1, $envelopes);
        $envelope = $envelopes[0];
        static::assertEquals($body, $envelope->getMessage());
        static::assertSame($attributes, $envelope->last(AttributesStamp::class)?->getAttributes());
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

    public function testItThrowsAnExceptionInsteadOfRejecting(): void
    {
        $this->expectException(TransportException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No GpsReceivedStamp found on the Envelope.');

        $this->gpsReceiver->reject(EnvelopeFactory::create());
    }
}
