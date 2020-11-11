<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Transport;

use Google\Cloud\PubSub\PubSubClient;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * @author Ronald Marfoldi <ronald.marfoldi@petitpress.sk>
 */
final class GpsTransportFactory implements TransportFactoryInterface
{
    /**
     * @var GpsConfigurationResolverInterface
     */
    private GpsConfigurationResolverInterface $gpsConfigurationResolver;

    public function __construct(GpsConfigurationResolverInterface $gpsConfigurationResolver)
    {
        $this->gpsConfigurationResolver = $gpsConfigurationResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        return new GpsTransport(
            new PubSubClient(),
            $this->gpsConfigurationResolver->resolve($dsn, $options),
            $serializer
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $dsn, array $options): bool
    {
        return str_starts_with($dsn, 'gps://');
    }
}