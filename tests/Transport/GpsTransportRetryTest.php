<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Tests\Transport;

use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Subscription;
use Google\Cloud\PubSub\Topic;
use PetitPress\GpsMessengerBundle\Transport\GpsConfigurationInterface;
use PetitPress\GpsMessengerBundle\Transport\GpsTransport;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\EventListener\SendFailedMessageForRetryListener;
use Symfony\Component\Messenger\EventListener\StopWorkerOnMessageLimitListener;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Retry\MultiplierRetryStrategy;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Worker;

/**
 * A message goes through the Symfony Messenger worker and its retry listener, as in messenger:consume.
 *
 * @author Ronald Marfoldi <ronald.marfoldi@petitpress.sk>
 */
final class GpsTransportRetryTest extends TestCase
{
    private const TRANSPORT_NAME = 'gps';

    /**
     * @var Subscription&MockObject
     */
    private MockObject $subscriptionMock;

    /**
     * @var Topic&MockObject
     */
    private MockObject $topicMock;

    private Message $gpsMessage;

    protected function setUp(): void
    {
        $this->gpsMessage = new Message(
            ['data' => json_encode((new PhpSerializer())->encode(new Envelope(new stdClass())), JSON_THROW_ON_ERROR)],
            ['ackId' => 'ack-id'],
        );
        $this->subscriptionMock = $this->createMock(Subscription::class);
        $this->subscriptionMock->method('pull')->willReturn([$this->gpsMessage]);
        $this->topicMock = $this->createMock(Topic::class);
    }

    public function testAFailedMessageIsLeftToPubSubRedelivery(): void
    {
        $this->subscriptionMock->expects(static::once())->method('modifyAckDeadline')->with($this->gpsMessage, 0);
        $this->subscriptionMock->expects(static::never())->method('acknowledge');
        $this->topicMock->expects(static::never())->method('publish');

        $this->consumeOneMessage(static function (): void {
            throw new RuntimeException('The handler failed.');
        });
    }

    public function testAHandledMessageIsAcknowledged(): void
    {
        $this->subscriptionMock->expects(static::once())->method('acknowledge')->with($this->gpsMessage);
        $this->subscriptionMock->expects(static::never())->method('modifyAckDeadline');

        $this->consumeOneMessage(static function (): void {
        });
    }

    private function consumeOneMessage(callable $handler): void
    {
        $gpsConfigurationStub = $this->createStub(GpsConfigurationInterface::class);
        $gpsConfigurationStub->method('getTopicName')->willReturn('topic-name');
        $gpsConfigurationStub->method('getSubscriptionName')->willReturn('subscription-name');
        $gpsConfigurationStub->method('getSubscriptionPullOptions')->willReturn([]);

        $pubSubClientStub = $this->createStub(PubSubClient::class);
        $pubSubClientStub->method('subscription')->willReturn($this->subscriptionMock);
        $pubSubClientStub->method('topic')->willReturn($this->topicMock);

        $transport = new GpsTransport($pubSubClientStub, $gpsConfigurationStub, new PhpSerializer());

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(new SendFailedMessageForRetryListener(
            new ServiceLocator([self::TRANSPORT_NAME => static fn (): GpsTransport => $transport]),
            new ServiceLocator([self::TRANSPORT_NAME => static fn (): MultiplierRetryStrategy => new MultiplierRetryStrategy()]),
        ));
        $eventDispatcher->addSubscriber(new StopWorkerOnMessageLimitListener(1));

        $bus = new MessageBus([
            new HandleMessageMiddleware(new HandlersLocator([stdClass::class => [$handler]])),
        ]);

        (new Worker([self::TRANSPORT_NAME => $transport], $bus, $eventDispatcher))->run();
    }
}
